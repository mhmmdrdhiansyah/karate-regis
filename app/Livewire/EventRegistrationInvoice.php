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
        $draftItems = RegistrationDraftItem::query()
            ->where('registration_draft_id', $this->draftId)
            ->whereNotNull('sub_category_id')
            ->with(['participant', 'subCategory.eventCategory'])
            ->get();

        if ($draftItems->isEmpty()) {
            return collect();
        }

        return $draftItems->groupBy('sub_category_id')
            ->map(function (Collection $items, int $subCategoryId) {
                $subCategory = $items->first()->subCategory;
                
                $athletes = $items->map(function ($item) {
                    return [
                        'participant' => $item->participant,
                        'team_group_id' => $item->team_group_id,
                    ];
                });

                return [
                    'subCategory' => $subCategory,
                    'athletes' => $athletes,
                ];
            })
            ->values();
    }

    #[Computed]
    public function totalAthleteFee(): float
    {
        if ($this->athleteSelections->count() === 0) {
            return 0;
        }

        return $this->athleteSelections->sum(function (array $selection) {
            if ($selection['subCategory']->isTeam()) {
                // Hitung jumlah tim unik dari atlet
                $teamCount = collect($selection['athletes'])
                    ->pluck('team_group_id')
                    ->filter()
                    ->unique()
                    ->count();

                // Minimal 1 jika ada atlet tapi belum ada team_group_id (backward compat)
                $teamCount = max($teamCount, 1);

                return (float) $selection['subCategory']->price * $teamCount;
            }
            // Biaya Individu dikali jumlah atlet
            return (float) $selection['subCategory']->price * count($selection['athletes']);
        });
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
            foreach ($this->athleteSelections as $selection) {
                $subCategory = $selection['subCategory'];
                $athletes = collect($selection['athletes']);

                if ($subCategory->isTeam()) {
                    // Validasi per tim
                    $teams = $athletes->groupBy('team_group_id');
                    
                    if ($teams->isEmpty()) {
                        $this->errorMessage = "Sub-kategori {$subCategory->name} harus memiliki minimal 1 tim.";
                        return;
                    }

                    foreach ($teams as $teamId => $teamAthletes) {
                        if (empty($teamId)) {
                            $this->errorMessage = "Terdapat atlet yang belum dimasukkan ke dalam tim pada {$subCategory->name}.";
                            return;
                        }

                        $count = $teamAthletes->count();
                        if ($count < $subCategory->min_participants || $count > $subCategory->max_participants) {
                            $this->errorMessage = "Tim pada {$subCategory->name} harus berisi {$subCategory->min_participants}-{$subCategory->max_participants} atlet.";
                            return;
                        }
                    }
                } else {
                    // Validasi individu
                    $athleteCount = $athletes->count();
                    if ($athleteCount < $subCategory->min_participants || $athleteCount > $subCategory->max_participants) {
                        $this->errorMessage = "Jumlah atlet pada {$subCategory->name} tidak sesuai ketentuan.";
                        return;
                    }
                }

                // Cek eligibility
                $athleteIds = $athletes->pluck('participant.id')->toArray();
                $eligibleIds = Participant::eligibleFor($subCategory)
                    ->whereIn('id', $athleteIds)
                    ->pluck('id')
                    ->toArray();

                if (count($eligibleIds) !== count($athleteIds)) {
                    $this->errorMessage = "Terdapat atlet pada {$subCategory->name} yang tidak memenuhi syarat.";
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

        try {
            DB::transaction(function () use ($contingent, $event) {
                $payment = Payment::create([
                    'contingent_id' => $contingent->id,
                    'event_id' => $event->id,
                    'total_amount' => $this->totalAmount,
                    'status' => PaymentStatus::Pending->value,
                ]);

                $athleteRegistrations = [];
            foreach ($this->athleteSelections as $selection) {
                foreach ($selection['athletes'] as $athleteData) {
                    $athlete = $athleteData['participant'];
                    $athleteRegistrations[] = [
                        'participant_id' => $athlete->id,
                        'payment_id' => $payment->id,
                        'sub_category_id' => $selection['subCategory']->id,
                        'team_group_id' => $athleteData['team_group_id'] ?? null,
                        'status_berkas' => RegistrationStatus::Unsubmitted->value,
                        'verified_at' => null,
                        'verified_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

                $coachRegistrations = $this->coaches->map(fn ($coach) => [
                    'participant_id' => $coach->id,
                    'payment_id' => $payment->id,
                    'sub_category_id' => null,
                    'team_group_id' => null,
                    'status_berkas' => RegistrationStatus::Verified->value,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating invoice: ' . $e->getMessage());
            $this->errorMessage = 'Terjadi kesalahan sistem saat membuat invoice: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.event-registration-invoice');
    }
}
