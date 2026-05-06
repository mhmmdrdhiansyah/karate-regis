<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CoachSelectionForm extends Component
{
    // Properties
    public ?int $selectedEventId = null;
    public array $selectedCoachIds = [];
    public string $errorMessage = '';
    public string $search = '';
    public bool $showSavedIndicator = false;

    // Lifecycle
    public function mount(): void
    {
        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            abort(403, 'Anda belum memiliki data kontingen.');
        }
    }

    // Actions
    public function updatedSelectedEventId(string|int|null $value): void
    {
        $this->errorMessage = '';
        $this->showSavedIndicator = false;

        // Handle empty/invalid values
        if (empty($value) || !is_numeric($value)) {
            $this->selectedEventId = null;
            $this->selectedCoachIds = [];
            return;
        }

        $this->selectedEventId = (int) $value;

        if (! $this->selectedEventId) {
            $this->selectedCoachIds = [];
            return;
        }

        $registrationService = app(RegistrationService::class);
        $event = Event::find($this->selectedEventId);

        if (! $event || ! $registrationService->isRegistrationOpen($event)) {
            $this->errorMessage = 'Event tidak valid atau pendaftaran sudah ditutup.';
            $this->selectedEventId = null;
            $this->selectedCoachIds = [];
            return;
        }

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            $this->errorMessage = 'Anda belum memiliki data kontingen.';
            return;
        }

        // Load selected coaches for this event
        $this->selectedCoachIds = $this->getRegisteredCoachIds();
    }

    public function updatedSelectedCoachIds(): void
    {
        $this->errorMessage = '';
        $this->showSavedIndicator = false;
        $this->selectedCoachIds = array_values(array_unique(array_map('intval', $this->selectedCoachIds)));

        if (! $this->selectedEventId) {
            return;
        }

        $registrationService = app(RegistrationService::class);
        $event = Event::find($this->selectedEventId);

        if (! $event || ! $registrationService->isRegistrationOpen($event)) {
            $this->errorMessage = 'Pendaftaran event sudah ditutup.';
            $this->selectedCoachIds = $this->getRegisteredCoachIds();
            return;
        }

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return;
        }

        // Get draft
        $draft = \App\Models\RegistrationDraft::firstOrCreate([
            'contingent_id' => $contingent->id,
            'event_id' => $this->selectedEventId,
            'status' => 'draft',
        ]);

        // Get confirmed coach IDs (those already in a verified/pending payment)
        $confirmedCoachIds = $this->getConfirmedCoachIds();

        // If user tries to uncheck a confirmed coach, warn them and re-add it
        $tryingToRemoveConfirmed = count(array_diff($confirmedCoachIds, $this->selectedCoachIds)) > 0;
        if ($tryingToRemoveConfirmed) {
            $this->errorMessage = 'Pelatih yang sudah masuk invoice confirmed tidak dapat diubah.';
            $this->selectedCoachIds = array_values(array_unique(array_merge($this->selectedCoachIds, $confirmedCoachIds)));
        }

        // Validate coach IDs (must belong to contingent and be of type Coach)
        $validCoachIds = $this->coaches()->pluck('id')->toArray();
        $this->selectedCoachIds = array_values(array_intersect($this->selectedCoachIds, $validCoachIds));

        // Get current coach IDs in draft
        $draftCoachIds = \App\Models\RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNull('sub_category_id')
            ->pluck('participant_id')
            ->toArray();

        // Coaches to insert into draft
        $toInsert = array_diff($this->selectedCoachIds, $draftCoachIds);
        $toInsert = array_diff($toInsert, $confirmedCoachIds); // Don't re-insert confirmed ones

        // Coaches to delete from draft
        $toDelete = array_diff($draftCoachIds, $this->selectedCoachIds);

        if (count($toInsert) > 0) {
            $rows = collect($toInsert)->map(fn ($id) => [
                'registration_draft_id' => $draft->id,
                'participant_id' => $id,
                'sub_category_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            \App\Models\RegistrationDraftItem::insert($rows);
        }

        if (count($toDelete) > 0) {
            \App\Models\RegistrationDraftItem::query()
                ->where('registration_draft_id', $draft->id)
                ->whereNull('sub_category_id')
                ->whereIn('participant_id', $toDelete)
                ->delete();
        }

        $this->showSavedIndicator = true;
    }

    // Computed Properties
    #[Computed]
    public function events(): Collection
    {
        $registrationService = app(RegistrationService::class);
        return $registrationService->getOpenEvents();
    }

    #[Computed]
    public function selectedEvent(): ?Event
    {
        return $this->selectedEventId ? Event::find($this->selectedEventId) : null;
    }

    #[Computed]
    public function coaches(): Collection
    {
        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return collect();
        }

        return Participant::coaches()
            ->where('contingent_id', $contingent->id)
            ->when($this->search !== '', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function registeredCoachIds(): array
    {
        return $this->getRegisteredCoachIds();
    }

    #[Computed]
    public function confirmedCoachIds(): array
    {
        return $this->getConfirmedCoachIds();
    }

    // Render
    public function render()
    {
        return view('livewire.coach-selection-form');
    }

    // Private Methods
    private function getRegisteredCoachIds(): array
    {
        if (! $this->selectedEventId) {
            return [];
        }

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return [];
        }

        // Combine IDs from Draft and existing active Registrations
        $draftIds = \App\Models\RegistrationDraftItem::query()
            ->whereHas('draft', function ($query) use ($contingent) {
                $query->where('contingent_id', $contingent->id)
                    ->where('event_id', $this->selectedEventId)
                    ->where('status', 'draft');
            })
            ->whereNull('sub_category_id')
            ->pluck('participant_id')
            ->toArray();

        $activeIds = Registration::query()
            ->whereHas('participant', function ($query) use ($contingent) {
                $query->where('contingent_id', $contingent->id);
            })
            ->whereNull('sub_category_id')
            ->whereHas('payment', function ($query) {
                $query->where('event_id', $this->selectedEventId)
                    ->where('status', '!=', \App\Enums\PaymentStatus::Cancelled);
            })
            ->pluck('participant_id')
            ->toArray();

        return array_values(array_unique(array_merge($draftIds, $activeIds)));
    }

    private function getConfirmedCoachIds(): array
    {
        if (! $this->selectedEventId) {
            return [];
        }

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            return [];
        }

        return Registration::query()
            ->whereHas('participant', function ($query) use ($contingent) {
                $query->where('contingent_id', $contingent->id);
            })
            ->whereNull('sub_category_id')
            ->whereHas('payment', function ($query) {
                $query->where('event_id', $this->selectedEventId)
                    ->where('status', '!=', \App\Enums\PaymentStatus::Cancelled);
            })
            ->pluck('participant_id')
            ->unique()
            ->toArray();
    }


}
