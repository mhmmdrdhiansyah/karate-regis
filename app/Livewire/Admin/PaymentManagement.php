<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentStatus;
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
    public $rejectionReason = '';
    public ?int $selectedPaymentId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    #[Computed]
    public function payments()
    {
        return Payment::query()
            ->with(['contingent', 'event'])
            ->when($this->search, function ($query) {
                $query->whereHas('contingent', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('official_name', 'like', '%' . $this->search . '%');
                })->orWhere('id', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
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

        $payment = Payment::findOrFail($this->selectedPaymentId);

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
                    ->update([
                        'status_berkas' => RegistrationStatus::PendingReview->value,
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

        $payment = Payment::findOrFail($this->selectedPaymentId);

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

        $payment = Payment::findOrFail($this->selectedPaymentId);

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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.payment-management');
    }
}
