<?php

namespace App\Livewire\Admin;

use App\Enums\ParticipantType;
use App\Enums\RegistrationStatus;
use App\Models\ActivityLog;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Contingent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ParticipantManagement extends Component
{
    use WithPagination;

    // Filter & Search
    public $search = '';
    public $typeFilter = '';
    public $verificationFilter = '';
    public $provinceFilter = '';

    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Actions
    public ?int $selectedParticipantId = null;
    public $rejectionReason = '';
    
    // Cache
    public array $duplicateNiks = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'verificationFilter' => ['except' => ''],
        'provinceFilter' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->loadDuplicateNiks();
    }

    public function loadDuplicateNiks()
    {
        $this->duplicateNiks = Participant::select('nik')
            ->whereNotNull('nik')
            ->where('nik', '!=', '')
            ->groupBy('nik')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nik')
            ->toArray();
    }

    #[Computed]
    public function participants()
    {
        return Participant::query()
            ->with(['contingent', 'verifiedBy'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nik', 'like', '%' . $this->search . '%')
                        ->orWhereHas('contingent', function ($c) {
                            $c->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('official_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->verificationFilter !== '', function ($q) {
                $q->where('is_verified', $this->verificationFilter === 'verified');
            })
            ->when($this->provinceFilter, function ($q) {
                $q->whereHas('contingent', fn($c) => $c->where('province', $this->provinceFilter));
            })
            // Sorting by contingent needs a join or subquery for performance
            ->when($this->sortField === 'contingent', function($q) {
                $q->join('contingents', 'participants.contingent_id', '=', 'contingents.id')
                  ->select('participants.*')
                  ->orderBy('contingents.name', $this->sortDirection);
            }, function($q) {
                $q->orderBy($this->sortField, $this->sortDirection);
            })
            ->paginate(15);
    }

    #[Computed]
    public function provinces()
    {
        return Contingent::whereNotNull('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function selectParticipant(int $id)
    {
        $this->selectedParticipantId = $id;
        $this->rejectionReason = '';
        $this->resetErrorBag();
    }

    public function approve()
    {
        if (!$this->selectedParticipantId) return;

        $participant = Participant::findOrFail($this->selectedParticipantId);
        
        if ($participant->is_verified) {
            $this->dispatch('swal:error', message: 'Peserta ini sudah diverifikasi.');
            return;
        }

        try {
            DB::transaction(function () use ($participant) {
                $participant->update([
                    'is_verified' => true,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ]);

                Registration::where('participant_id', $participant->id)
                    ->where('status_berkas', RegistrationStatus::PendingReview->value)
                    ->update([
                        'status_berkas' => RegistrationStatus::Verified->value,
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                        'rejection_reason' => null,
                    ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'participant.verified',
                    'subject_type' => 'Participant',
                    'subject_id' => $participant->id,
                    'description' => "Admin memverifikasi dokumen peserta: {$participant->name} (Via Management)",
                ]);
            });

            $this->selectedParticipantId = null;
            session()->flash('success', 'Dokumen peserta berhasil diverifikasi.');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            Log::error("Error approving participant {$participant->id}: " . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|min:5',
        ]);

        $participant = Participant::findOrFail($this->selectedParticipantId);

        try {
            DB::transaction(function () use ($participant) {
                $participant->update([
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                ]);

                Registration::where('participant_id', $participant->id)
                    ->where('status_berkas', RegistrationStatus::PendingReview->value)
                    ->update([
                        'status_berkas' => RegistrationStatus::Rejected->value,
                        'rejection_reason' => $this->rejectionReason,
                    ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'participant.rejected',
                    'subject_type' => 'Participant',
                    'subject_id' => $participant->id,
                    'description' => "Admin menolak dokumen peserta: {$participant->name}. Alasan: {$this->rejectionReason}",
                ]);
            });

            $this->selectedParticipantId = null;
            session()->flash('success', 'Dokumen peserta berhasil ditolak.');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            Log::error("Error rejecting participant {$participant->id}: " . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function revoke()
    {
        $this->validate([
            'rejectionReason' => 'required|min:5',
        ]);

        $participant = Participant::findOrFail($this->selectedParticipantId);

        if (!$participant->is_verified) {
            $this->dispatch('swal:error', message: 'Hanya peserta terverifikasi yang bisa di-revoke.');
            return;
        }

        try {
            DB::transaction(function () use ($participant) {
                $participant->update([
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'participant.revoked',
                    'subject_type' => 'Participant',
                    'subject_id' => $participant->id,
                    'description' => "Admin me-revoke verifikasi peserta: {$participant->name}. Alasan: {$this->rejectionReason}",
                ]);
            });

            $this->selectedParticipantId = null;
            session()->flash('success', 'Verifikasi peserta berhasil dicabut.');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            Log::error("Error revoking participant {$participant->id}: " . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan sistem.');
        }
    }

    // Reset pagination on filter changes
    public function updatedSearch() { $this->resetPage(); }
    public function updatedTypeFilter() { $this->resetPage(); }
    public function updatedVerificationFilter() { $this->resetPage(); }
    public function updatedProvinceFilter() { $this->resetPage(); }

    public function render()
    {
        return view('livewire.admin.participant-management');
    }
}
