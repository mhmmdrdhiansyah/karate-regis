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
    public array $selectedCoachIds = [];

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

        $this->selectedCoachIds = $this->getDraftCoachIds();
        $this->selectedEventName = $event->name;
        $this->currentStep = 2;
    }

    public function updatedSelectedCoachIds(): void
    {
        $this->selectedCoachIds = array_values(array_unique(array_map('intval', $this->selectedCoachIds)));
        $draft = $this->getActiveDraft();
        if (! $draft) {
            return;
        }

        $registrationService = app(RegistrationService::class);
        $event = Event::find($this->selectedEventId);
        if (! $event || ! $registrationService->isRegistrationOpen($event)) {
            $this->errorMessage = 'Pendaftaran event sudah ditutup.';
            return;
        }

        $registeredIds = $this->getRegisteredCoachIds();
        if (count($registeredIds) > 0) {
            $this->selectedCoachIds = array_values(array_diff($this->selectedCoachIds, $registeredIds));
        }

        $validCoachIds = $this->getCoaches()->pluck('id')->toArray();
        $this->selectedCoachIds = array_values(array_intersect($this->selectedCoachIds, $validCoachIds));

        $existingIds = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNull('sub_category_id')
            ->pluck('participant_id')
            ->toArray();

        $toInsert = array_diff($this->selectedCoachIds, $existingIds);
        $toDelete = array_diff($existingIds, $this->selectedCoachIds);

        if (count($toInsert) > 0) {
            $rows = collect($toInsert)->map(fn ($id) => [
                'registration_draft_id' => $draft->id,
                'participant_id' => $id,
                'sub_category_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            RegistrationDraftItem::insert($rows);
        }

        if (count($toDelete) > 0) {
            RegistrationDraftItem::query()
                ->where('registration_draft_id', $draft->id)
                ->whereNull('sub_category_id')
                ->whereIn('participant_id', $toDelete)
                ->delete();
        }
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
        $this->selectedCoachIds = $this->getDraftCoachIds();
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
            $this->selectedCoachIds = [];
        } elseif ($step === 2) {
            $this->selectedCategoryId = null;
            $this->selectedCategoryName = '';
            $this->selectedSubCategoryId = null;
            $this->selectedCoachIds = $this->getDraftCoachIds();
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
                'coaches' => $this->getCoaches(),
                'draftSelections' => $this->getDraftSelections(),
                'selectedCoachCount' => $this->getSelectedCoachCount(),
                'registeredCoachIds' => $this->getRegisteredCoachIds(),
                'draftCoachIds' => $this->getDraftCoachIds(),
            ],
            default => [],
        };

        return view('livewire.event-registration-wizard', $data);
    }


    private function getSelectedCoachCount(): int
    {
        $draft = $this->getActiveDraft();
        if (! $draft) {
            return 0;
        }

        return RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNull('sub_category_id')
            ->count();
    }

    private function getDraftSelections(): Collection
    {
        $draft = $this->getActiveDraft();
        if (! $draft) {
            return collect();
        }

        $athleteCounts = RegistrationDraftItem::query()
            ->selectRaw('sub_category_id, COUNT(*) as total')
            ->where('registration_draft_id', $draft->id)
            ->whereNotNull('sub_category_id')
            ->groupBy('sub_category_id')
            ->get();

        if ($athleteCounts->count() === 0) {
            return collect();
        }

        $subCategories = SubCategory::whereIn('id', $athleteCounts->pluck('sub_category_id'))
            ->with('eventCategory')
            ->get()
            ->keyBy('id');

        return $athleteCounts
            ->map(function ($item) use ($subCategories) {
                $subCategory = $subCategories->get($item->sub_category_id);
                if (! $subCategory) {
                    return null;
                }

                return [
                    'subCategory' => $subCategory,
                    'athlete_count' => (int) $item->total,
                ];
            })
            ->filter()
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

    private function getCoaches(): Collection
    {
        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return collect();
        }

        return Participant::coaches()
            ->where('contingent_id', $contingent->id)
            ->orderBy('name')
            ->get();
    }

    private function getDraftCoachIds(): array
    {
        $draft = $this->getActiveDraft();
        if (! $draft) {
            return [];
        }

        return RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNull('sub_category_id')
            ->pluck('participant_id')
            ->unique()
            ->toArray();
    }

    private function getRegisteredCoachIds(): array
    {
        if (! $this->selectedEventId) {
            return [];
        }

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return [];
        }

        return Registration::query()
            ->whereNull('sub_category_id')
            ->whereHas('payment', function ($query) {
                $query->where('event_id', $this->selectedEventId)
                    ->where('status', '!=', \App\Enums\PaymentStatus::Cancelled->value);
            })
            ->whereHas('participant', function ($query) use ($contingent) {
                $query->where('contingent_id', $contingent->id);
            })
            ->pluck('participant_id')
            ->unique()
            ->toArray();
    }
}
