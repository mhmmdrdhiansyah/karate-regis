<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventCategory;
use App\Services\RegistrationService;
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
        // Inisialisasi awal. Data daftar event akan diload di render()
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
                'events' => $registrationService->getOpenEvents()
            ],
            2 => [
                'categoriesGrouped' => $registrationService->getCategoriesForEvent($this->selectedEventId)
            ],
            3 => [
                'subCategories' => EventCategory::find($this->selectedCategoryId)?->subCategories ?? collect()
            ],
            default => [],
        };

        return view('livewire.event-registration-wizard', $data);
    }
}
