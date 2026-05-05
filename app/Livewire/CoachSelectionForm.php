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

        // Get confirmed coach IDs
        $confirmedCoachIds = $this->getConfirmedCoachIds();

        // Remove confirmed coaches from selection
        $this->selectedCoachIds = array_values(array_diff($this->selectedCoachIds, $confirmedCoachIds));

        if (count($confirmedCoachIds) > 0) {
            $this->errorMessage = 'Pelatih yang sudah masuk invoice confirmed tidak dapat diubah.';
        }

        // Validate coach IDs (must belong to contingent)
        $validCoachIds = $this->coaches()->pluck('id')->toArray();
        $this->selectedCoachIds = array_values(array_intersect($this->selectedCoachIds, $validCoachIds));

        // Get currently registered coaches
        $registeredCoachIds = $this->getRegisteredCoachIds();

        // Coaches to insert (newly selected)
        $toInsert = array_diff($this->selectedCoachIds, $registeredCoachIds);

        // Coaches to delete (unselected)
        $toDelete = array_diff($registeredCoachIds, $this->selectedCoachIds);

        // Insert new registrations - need to find or create payment first
        $payment = \App\Models\Payment::firstOrCreate(
            [
                'contingent_id' => $contingent->id,
                'event_id' => $this->selectedEventId,
                'status' => 'draft',
            ],
            [
                'total_amount' => 0,
                'transfer_proof' => null,
                'verified_at' => null,
                'verified_by' => null,
                'rejection_reason' => null,
            ]
        );

        foreach ($toInsert as $coachId) {
            // Check if already exists to prevent duplicates
            $exists = \App\Models\Registration::query()
                ->where('participant_id', $coachId)
                ->where('payment_id', $payment->id)
                ->whereNull('sub_category_id')
                ->exists();

            if (! $exists) {
                \App\Models\Registration::create([
                    'participant_id' => $coachId,
                    'payment_id' => $payment->id,
                    'sub_category_id' => null,
                    'status_berkas' => 'pending',
                    'verified_at' => null,
                    'verified_by' => null,
                ]);
            }
        }

        // Delete unselected registrations
        if (count($toDelete) > 0) {
            \App\Models\Registration::query()
                ->whereHas('participant', function ($query) use ($contingent) {
                    $query->where('contingent_id', $contingent->id);
                })
                ->whereNull('sub_category_id')
                ->where('payment_id', $payment->id)
                ->whereIn('participant_id', $toDelete)
                ->delete();
        }
    }

    // Computed Properties
    #[Computed]
    public function events(): Collection
    {
        $registrationService = app(RegistrationService::class);
        return $registrationService->getOpenEvents();
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
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function registeredCoachIds(): array
    {
        return $this->getRegisteredCoachIds();
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

        return Registration::query()
            ->whereHas('participant', function ($query) use ($contingent) {
                $query->where('contingent_id', $contingent->id);
            })
            ->whereNull('sub_category_id')
            ->whereHas('payment', function ($query) {
                $query->where('event_id', $this->selectedEventId)
                    ->where('status', '!=', 'cancelled');
            })
            ->pluck('participant_id')
            ->unique()
            ->toArray();
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
                    ->where('status', 'confirmed');
            })
            ->pluck('participant_id')
            ->unique()
            ->toArray();
    }
}
