<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\SubCategory;
use App\Services\RegistrationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class EventRegistrationInvoice extends Component
{
    #[Locked]
    public int $eventId;

    #[Locked]
    public ?int $subCategoryId = null;

    public array $selectedSubCategories = [];
    public array $selectedCoachIds = [];
    public string $errorMessage = '';
    public bool $showConfirmation = false;
    public ?int $draftId = null;

    public function mount(int $event): void
    {
        $this->eventId = $event;

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            abort(403, 'Anda belum memiliki data kontingen.');
        }

        $draft = RegistrationDraft::where('event_id', $this->eventId)
            ->where('contingent_id', $contingent->id)
            ->where('status', 'draft')
            ->first();

        if (! $draft) {
            session()->flash('error', 'Draft pendaftaran tidak ditemukan untuk event ini.');
            $this->redirect(route('registration.index'), navigate: true);
            return;
        }

        $this->draftId = $draft->id;

        $athleteItems = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNotNull('sub_category_id')
            ->get();

        foreach ($athleteItems as $item) {
            $this->selectedSubCategories[$item->sub_category_id][] = $item->participant_id;
        }

        $this->selectedCoachIds = RegistrationDraftItem::query()
            ->where('registration_draft_id', $draft->id)
            ->whereNull('sub_category_id')
            ->pluck('participant_id')
            ->unique()
            ->values()
            ->toArray();

        if (count($this->selectedSubCategories) === 0 && count($this->selectedCoachIds) === 0) {
            session()->flash('error', 'Draft pendaftaran masih kosong. Silakan pilih atlet atau pelatih terlebih dahulu.');
            $this->redirect(route('registration.index'), navigate: true);
            return;
        }

        if (count($this->selectedSubCategories) === 0 && count($this->selectedCoachIds) === 0) {
            session()->flash('error', 'Draft pendaftaran masih kosong. Silakan pilih atlet atau pelatih terlebih dahulu.');
            $this->redirect(route('registration.index'), navigate: true);
            return;
        }
    }

    #[Computed]
    public function event(): Event
    {
        return Event::findOrFail($this->eventId);
    }

    #[Computed]
    public function subCategory(): ?SubCategory
    {
        return null;
    }

    #[Computed]
    public function athletes(): Collection
    {
        return collect();
    }

    #[Computed]
    public function coaches(): Collection
    {
        if (count($this->selectedCoachIds) === 0) {
            return collect();
        }

        return Participant::coaches()
            ->where('contingent_id', auth()->user()->contingent->id)
            ->whereIn('id', $this->selectedCoachIds)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function athleteSelections(): Collection
    {
        if (count($this->selectedSubCategories) === 0) {
            return collect();
        }

        $subCategories = SubCategory::whereIn('id', array_keys($this->selectedSubCategories))
            ->with('eventCategory')
            ->get()
            ->keyBy('id');

        $allAthleteIds = collect($this->selectedSubCategories)->flatten()->unique()->values()->all();
        $athletes = Participant::athletes()
            ->where('contingent_id', auth()->user()->contingent->id)
            ->whereIn('id', $allAthleteIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return collect($this->selectedSubCategories)
            ->map(function (array $athleteIds, int $subCategoryId) use ($subCategories, $athletes) {
                $subCategory = $subCategories->get($subCategoryId);
                if (! $subCategory) {
                    return null;
                }

                $list = collect($athleteIds)
                    ->map(fn ($athleteId) => $athletes->get($athleteId))
                    ->filter()
                    ->values();

                return [
                    'subCategory' => $subCategory,
                    'athletes' => $list,
                ];
            })
            ->filter()
            ->values();
    }

    #[Computed]
    public function totalAthleteFee(): float
    {
        if ($this->athleteSelections->count() === 0) {
            return 0;
        }

        if ($this->athleteSelections->count() > 0) {
            return $this->athleteSelections->sum(function (array $selection) {
                return (float) $selection['subCategory']->price * $selection['athletes']->count();
            });
        }

        return 0;
    }

    #[Computed]
    public function totalCoachFee(): float
    {
        if ($this->coaches->count() === 0) {
            return 0;
        }

        return (float) $this->event->coach_fee * $this->coaches->count();
    }

    #[Computed]
    public function totalAmount(): float
    {
        return (float) $this->event->event_fee + $this->totalAthleteFee + $this->totalCoachFee;
    }

    public function confirmSubmit(): void
    {
        $this->errorMessage = '';
        $this->showConfirmation = true;
    }

    public function cancelConfirmation(): void
    {
        $this->showConfirmation = false;
    }

    public function submit(RegistrationService $registrationService): void
    {
        $this->errorMessage = '';
        $event = $this->event;

        if (! $registrationService->isRegistrationOpen($event)) {
            $this->errorMessage = 'Pendaftaran event sudah ditutup.';
            return;
        }

        $contingent = auth()->user()->contingent;
        if (! $contingent) {
            $this->errorMessage = 'Anda belum memiliki data kontingen.';
            return;
        }

        if ($registrationService->hasExistingPayment($contingent->id, $event->id)) {
            $this->errorMessage = 'Anda sudah memiliki invoice aktif untuk event ini.';
            return;
        }

        if (count($this->selectedSubCategories) > 0) {
            foreach ($this->selectedSubCategories as $subCategoryId => $athleteIds) {
                $subCategory = SubCategory::with('eventCategory')->find($subCategoryId);
                if (! $subCategory || $subCategory->eventCategory->event_id !== $event->id) {
                    $this->errorMessage = 'Sub-kategori tidak valid untuk event ini.';
                    return;
                }

                $athleteCount = count($athleteIds);
                if ($athleteCount < $subCategory->min_participants || $athleteCount > $subCategory->max_participants) {
                    $this->errorMessage = 'Jumlah atlet tidak sesuai dengan ketentuan sub-kategori.';
                    return;
                }

                $eligibleIds = Participant::eligibleFor($subCategory)
                    ->whereIn('id', $athleteIds)
                    ->pluck('id')
                    ->toArray();

                if (count($eligibleIds) !== $athleteCount) {
                    $this->errorMessage = 'Terdapat atlet yang tidak memenuhi syarat.';
                    return;
                }
            }
        }

        $registeredCoachIds = Registration::query()
            ->whereNull('sub_category_id')
            ->whereHas('payment', function ($query) use ($event) {
                $query->where('event_id', $event->id)
                    ->where('status', '!=', PaymentStatus::Cancelled->value);
            })
            ->pluck('participant_id')
            ->unique()
            ->toArray();

        $duplicateCoachIds = array_intersect($registeredCoachIds, $this->selectedCoachIds);
        if (count($duplicateCoachIds) > 0) {
            $this->errorMessage = 'Sebagian pelatih sudah terdaftar di event ini.';
            return;
        }

        if (count($this->selectedCoachIds) !== $this->coaches->count()) {
            $this->errorMessage = 'Terdapat pelatih yang tidak valid.';
            return;
        }

        if ($this->athleteSelections->count() === 0 && $this->coaches->count() === 0) {
            $this->errorMessage = 'Pilih minimal satu atlet atau pelatih untuk membuat invoice.';
            return;
        }

        DB::transaction(function () use ($contingent, $event) {
            $payment = Payment::create([
                'contingent_id' => $contingent->id,
                'event_id' => $event->id,
                'total_amount' => $this->totalAmount,
                'status' => PaymentStatus::Pending->value,
            ]);

            $athleteRegistrations = [];
            foreach ($this->athleteSelections as $selection) {
                foreach ($selection['athletes'] as $athlete) {
                    $athleteRegistrations[] = [
                        'participant_id' => $athlete->id,
                        'payment_id' => $payment->id,
                        'sub_category_id' => $selection['subCategory']->id,
                        'status_berkas' => RegistrationStatus::Unsubmitted->value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $coachRegistrations = $this->coaches->map(fn ($coach) => [
                'participant_id' => $coach->id,
                'payment_id' => $payment->id,
                'sub_category_id' => null,
                'status_berkas' => RegistrationStatus::Verified->value,
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            $payload = array_merge($athleteRegistrations, $coachRegistrations);
            if (count($payload) > 0) {
                Registration::insert($payload);
            }

            if ($this->draftId) {
                RegistrationDraftItem::query()
                    ->where('registration_draft_id', $this->draftId)
                    ->delete();

                RegistrationDraft::query()
                    ->where('id', $this->draftId)
                    ->update(['status' => 'converted']);
            }
        });

        session()->flash('success', 'Invoice berhasil dibuat. Silakan lanjutkan pembayaran.');
        $this->redirect(route('registration.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.event-registration-invoice');
    }
}
