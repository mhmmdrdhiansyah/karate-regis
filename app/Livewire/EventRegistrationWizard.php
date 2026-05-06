<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\SubCategory;
use App\Services\RegistrationService;
use Illuminate\Support\Collection;
use Livewire\Component;

class EventRegistrationWizard extends Component
{
    public int $currentStep = 1;
    public ?int $selectedEventId = null;
    public ?int $selectedCategoryId = null;
    public ?int $selectedSubCategoryId = null;

    public string $selectedEventName = '';
    public string $selectedCategoryName = '';
    public string $errorMessage = '';

    public function mount(): void
    {
    }

    public function selectEvent(int $eventId, RegistrationService $registrationService): void
    {
        $this->errorMessage = '';

        $event = Event::find($eventId);
        if (! $event || ! $registrationService->isRegistrationOpen($event)) {
            $this->errorMessage = 'Event tidak valid atau pendaftaran sudah ditutup.';
            return;
        }

        // PENTING: User harus punya contingent. Di asumsikan auth()->user()->contingent ada.
        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            $this->errorMessage = 'Anda belum memiliki data kontingen. Silakan lengkapi profil Anda.';
            return;
        }

        if ($registrationService->hasExistingPayment($contingent->id, $eventId)) {
            $this->errorMessage = 'Anda sudah memiliki invoice aktif untuk event ini. Silakan cek di halaman pembayaran atau batalkan terlebih dahulu sebelum mendaftar ulang.';
            return;
        }

        $this->selectedEventId = $eventId;
        $this->ensureDraft($eventId, $contingent->id);

        $this->selectedEventName = $event->name;
        $this->currentStep = 2;
    }

    public function selectCategory(int $categoryId): void
    {
        $category = EventCategory::find($categoryId);
        if (! $category || $category->event_id !== $this->selectedEventId) {
            $this->errorMessage = 'Kategori tidak valid.';
            return;
        }

        $this->selectedCategoryId = $categoryId;
        $this->selectedCategoryName = $category->type->value . ' - ' . $category->class_name;
        $this->currentStep = 3;
    }

    public function selectSubCategory(int $subCategoryId): void
    {
        $this->selectedSubCategoryId = $subCategoryId;
        
        // Step 4.2 akan menghandle form pendaftaran atlet.
        // Redirect dengan mengirimkan ID event, category, dan sub-category
        $this->redirect(route('registration.create', [
            'event' => $this->selectedEventId,
            'category' => $this->selectedCategoryId,
            'sub_category' => $this->selectedSubCategoryId,
        ]), navigate: true);
    }

    public function goToStep(int $step): void
    {
        if ($step >= $this->currentStep) {
            return; // Hanya bisa kembali ke step sebelumnya
        }

        $this->currentStep = $step;
        $this->errorMessage = '';

        if ($step === 1) {
            $this->selectedEventId = null;
            $this->selectedEventName = '';
            $this->selectedCategoryId = null;
            $this->selectedCategoryName = '';
            $this->selectedSubCategoryId = null;
        } elseif ($step === 2) {
            $this->selectedCategoryId = null;
            $this->selectedCategoryName = '';
            $this->selectedSubCategoryId = null;
        }
    }

    public function render()
    {
        $registrationService = app(RegistrationService::class);

        $data = match ($this->currentStep) {
            1 => [
                'events' => $registrationService->getOpenEvents(),
                'statusLabels' => $registrationService->getOpenEvents()->mapWithKeys(
                    fn ($event) => [$event->id => $registrationService->getRegistrationStatusLabel($event)]
                ),
            ],
            2 => [
                'categoriesGrouped' => $registrationService->getCategoriesForEvent($this->selectedEventId)
            ],
            3 => [
                'subCategories' => EventCategory::find($this->selectedCategoryId)?->subCategories ?? collect(),
                'draftSelections' => $this->getDraftSelections(),
            ],
            default => [],
        };

        return view('livewire.event-registration-wizard', $data);
    }


    private function getDraftSelections(): Collection
    {
        $draft = $this->getActiveDraft();
        if (! $draft) {
            return collect();
        }

        return RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNotNull('sub_category_id')
            ->with(['participant', 'subCategory.eventCategory'])
            ->get()
            ->groupBy('sub_category_id')
            ->map(function ($items, $subCategoryId) {
                $first = $items->first();
                $subCategory = $first->subCategory;
                
                $data = [
                    'subCategory' => $subCategory,
                    'athlete_count' => $items->count(),
                    'athlete_names' => $items->pluck('participant.name')->toArray(),
                ];

                if ($subCategory->isTeam()) {
                    $data['team_count'] = $items->pluck('team_group_id')->filter()->unique()->count();
                }

                return $data;
            })
            ->values();
    }

    private function ensureDraft(int $eventId, int $contingentId): RegistrationDraft
    {
        return RegistrationDraft::firstOrCreate([
            'contingent_id' => $contingentId,
            'event_id' => $eventId,
            'status' => 'draft',
        ]);
    }

    private function getActiveDraft(): ?RegistrationDraft
    {
        $contingent = auth()->user()->contingent;
        if (! $contingent || ! $this->selectedEventId) {
            return null;
        }

        return RegistrationDraft::where('contingent_id', $contingent->id)
            ->where('event_id', $this->selectedEventId)
            ->where('status', 'draft')
            ->first();
    }
}
