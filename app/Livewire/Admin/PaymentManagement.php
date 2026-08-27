<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PaymentManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $eventId = '';
    public $typeFilter = '';
    public $classFilter = '';
    public $subCategoryFilter = '';
    public $rejectionReason = '';
    public ?int $selectedPaymentId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'eventId' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'classFilter' => ['except' => ''],
        'subCategoryFilter' => ['except' => ''],
    ];

    #[Computed]
    public function events()
    {
        return \App\Models\Event::orderBy('name', 'asc')->get();
    }

    #[Computed]
    public function availableTypes()
    {
        $query = \App\Models\EventCategory::query();
        if ($this->eventId) {
            $query->where('event_id', $this->eventId);
        }
        return $query->pluck('type')->unique()->filter()->values();
    }

    #[Computed]
    public function availableClasses()
    {
        $query = \App\Models\EventCategory::query();
        if ($this->eventId) {
            $query->where('event_id', $this->eventId);
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        return $query->pluck('class_name')->unique()->filter()->values();
    }

    #[Computed]
    public function availableSubCategories()
    {
        return \App\Models\SubCategory::query()
            ->whereHas('eventCategory', function ($query) {
                if ($this->eventId) {
                    $query->where('event_id', $this->eventId);
                }
                if ($this->typeFilter) {
                    $query->where('type', $this->typeFilter);
                }
                if ($this->classFilter) {
                    $query->where('class_name', $this->classFilter);
                }
            })
            ->orderBy('name', 'asc')
            ->get();
    }

    #[Computed]
    public function payments()
    {
        return Payment::query()
            ->forManagedEvents()
            ->with(['contingent' => function ($query) {
                $query->withTrashed(); // Include soft-deleted contingents
            }, 'event', 'registrations.subCategory.eventCategory'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('contingent', function ($c) {
                        $c->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('official_name', 'like', '%' . $this->search . '%');
                    })->orWhere('id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->eventId, function ($query) {
                $query->where('event_id', $this->eventId);
            })
            ->when($this->typeFilter || $this->classFilter || $this->subCategoryFilter, function ($query) {
                $query->whereHas('registrations.subCategory', function ($q) {
                    if ($this->subCategoryFilter) {
                        $q->where('id', $this->subCategoryFilter);
                    }
                    if ($this->typeFilter || $this->classFilter) {
                        $q->whereHas('eventCategory', function ($ec) {
                            if ($this->typeFilter) {
                                $ec->where('type', $this->typeFilter);
                            }
                            if ($this->classFilter) {
                                $ec->where('class_name', $this->classFilter);
                            }
                        });
                    }
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function selectPayment(int $paymentId): void
    {
        $this->selectedPaymentId = $paymentId;
        $this->rejectionReason = '';
    }

    public function approve(): void
    {
        if (!$this->selectedPaymentId) return;

        $payment = Payment::forManagedEvents()->findOrFail($this->selectedPaymentId);

        if ($payment->event->status === EventStatus::Completed && !auth()->user()->hasRole('super-admin')) {
            $this->dispatch('swal:error', message: 'Event selesai, data read-only.');
            return;
        }

        if ($payment->status !== PaymentStatus::Pending) {
            $this->dispatch('swal:error', message: 'Hanya pembayaran berstatus pending yang bisa disetujui.');
            return;
        }

        try {
            DB::transaction(function () use ($payment) {
                // 1. Update Payment Status
                $payment->update([
                    'status' => PaymentStatus::Verified,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'rejection_reason' => null,
                ]);

                // 2. Update Registrations Status Berkas
                // Status berkas: unsubmitted -> pending_review
                Registration::where('payment_id', $payment->id)
                    ->where('status_berkas', RegistrationStatus::Unsubmitted->value)
                    ->whereHas('participant', fn ($q) => $q->where('is_verified', false))
                    ->update([
                        'status_berkas' => RegistrationStatus::PendingReview->value,
                    ]);

                // Atlet yang berkasnya sudah terverifikasi langsung jadi verified
                Registration::where('payment_id', $payment->id)
                    ->where('status_berkas', RegistrationStatus::Unsubmitted->value)
                    ->whereHas('participant', fn ($q) => $q->where('is_verified', true))
                    ->update([
                        'status_berkas' => RegistrationStatus::Verified->value,
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ]);
            });

            $this->selectedPaymentId = null;
            session()->flash('success', 'Pembayaran berhasil diverifikasi.');
            $this->dispatch('payment-processed');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(): void
    {
        $this->validate([
            'rejectionReason' => 'required|min:5',
        ], [
            'rejectionReason.required' => 'Alasan penolakan wajib diisi.',
            'rejectionReason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $payment = Payment::forManagedEvents()->findOrFail($this->selectedPaymentId);

        if ($payment->event->status === EventStatus::Completed && !auth()->user()->hasRole('super-admin')) {
            $this->dispatch('swal:error', message: 'Event selesai, data read-only.');
            return;
        }

        if ($payment->status !== PaymentStatus::Pending) {
            $this->dispatch('swal:error', message: 'Hanya pembayaran berstatus pending yang bisa ditolak.');
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'rejection_reason' => $this->rejectionReason,
        ]);

        $this->selectedPaymentId = null;
        $this->rejectionReason = '';
        
        session()->flash('success', 'Pembayaran berhasil ditolak.');
        $this->dispatch('payment-processed');
    }

    public function revoke(): void
    {
        $this->validate([
            'rejectionReason' => 'required|min:5',
        ], [
            'rejectionReason.required' => 'Alasan revoke wajib diisi.',
            'rejectionReason.min' => 'Alasan revoke minimal 5 karakter.',
        ]);

        $payment = Payment::forManagedEvents()->findOrFail($this->selectedPaymentId);

        if ($payment->event->status === EventStatus::Completed && !auth()->user()->hasRole('super-admin')) {
            $this->dispatch('swal:error', message: 'Event selesai, data read-only.');
            return;
        }

        if ($payment->status !== PaymentStatus::Verified) {
            $this->dispatch('swal:error', message: 'Hanya pembayaran berstatus verified yang bisa di-revoke.');
            return;
        }

        try {
            DB::transaction(function () use ($payment) {
                $oldStatus = $payment->status->value;

                // 1. Update Payment Status back to Pending
                $payment->update([
                    'status' => PaymentStatus::Pending,
                    'rejection_reason' => $this->rejectionReason,
                    'verified_at' => null,
                    'verified_by' => null,
                ]);

                // 2. Update Registrations Status Berkas
                // Hanya yang masih 'pending_review' yang dikembalikan ke 'unsubmitted'
                // Yang sudah 'verified' (berkas sudah dicek admin) tetap verified.
                Registration::where('payment_id', $payment->id)
                    ->where('status_berkas', RegistrationStatus::PendingReview->value)
                    ->update([
                        'status_berkas' => RegistrationStatus::Unsubmitted->value,
                    ]);

                // 3. Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'payment.revoked',
                    'subject_type' => 'Payment',
                    'subject_id' => $payment->id,
                    'description' => "Admin me-revoke verifikasi pembayaran #{$payment->id} milik kontingen {$payment->contingent->name}",
                    'properties' => [
                        'old_status' => $oldStatus,
                        'new_status' => PaymentStatus::Pending->value,
                        'reason' => $this->rejectionReason,
                    ],
                ]);
            });

            $this->selectedPaymentId = null;
            $this->rejectionReason = '';
            session()->flash('success', 'Verifikasi pembayaran berhasil dicabut (Revoked).');
            $this->dispatch('payment-processed');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = Payment::forManagedEvents()->findOrFail($paymentId);

        if ($payment->event->status === EventStatus::Completed && !auth()->user()->hasRole('super-admin')) {
            $this->dispatch('swal:error', message: 'Event selesai, data read-only.');
            return;
        }

        if ($payment->status === PaymentStatus::Verified) {
            $this->dispatch('swal:error', message: 'Pembayaran berstatus Verified tidak dapat langsung dihapus. Silakan Revoke statusnya terlebih dahulu jika benar-benar ingin menghapus.');
            return;
        }

        try {
            DB::transaction(function () use ($payment) {
                // Restore participants to draft before they are deleted
                app(\App\Services\RegistrationService::class)->restoreParticipantsToDraft($payment);

                // Delete linked registrations permanently to avoid foreign key constraint on payments
                Registration::where('payment_id', $payment->id)->forceDelete();

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'payment.deleted_by_admin',
                    'subject_type' => 'Payment',
                    'subject_id' => $payment->id,
                    'description' => "Admin menghapus pembayaran #{$payment->id} milik kontingen {$payment->contingent?->name}",
                    'properties' => [
                        'payment_id' => $payment->id,
                        'total_amount' => $payment->total_amount,
                        'status' => $payment->status->value,
                    ],
                ]);

                $payment->delete();
            });

            if ($this->selectedPaymentId === $paymentId) {
                $this->selectedPaymentId = null;
            }

            session()->flash('success', 'Pembayaran berhasil dihapus.');
            $this->dispatch('payment-processed');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedEventId(): void
    {
        $this->typeFilter = '';
        $this->classFilter = '';
        $this->subCategoryFilter = '';
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->classFilter = '';
        $this->subCategoryFilter = '';
        $this->resetPage();
    }

    public function updatedClassFilter(): void
    {
        $this->subCategoryFilter = '';
        $this->resetPage();
    }

    public function updatedSubCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.payment-management');
    }
}
