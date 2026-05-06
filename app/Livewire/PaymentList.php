<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Models\ActivityLog;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PaymentList extends Component
{
    use WithFileUploads;

    public $proofFile;
    public ?int $uploadingPaymentId = null;

    #[Computed]
    public function payments()
    {
        $contingent = auth()->user()->contingent;

        if (!$contingent) {
            return collect();
        }

        return Payment::where('contingent_id', $contingent->id)
            ->with(['event', 'registrations.participant', 'registrations.subCategory'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function startUpload(int $paymentId): void
    {
        $this->uploadingPaymentId = $paymentId;
        $this->proofFile = null;
    }

    public function cancelUpload(): void
    {
        $this->uploadingPaymentId = null;
        $this->proofFile = null;
    }

    public function uploadProof(): void
    {
        // 1. Validasi file
        $this->validate([
            'proofFile' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        // 2. Ambil payment dan cek authorization
        $payment = Payment::where('id', $this->uploadingPaymentId)
            ->where('contingent_id', auth()->user()->contingent->id)
            ->firstOrFail();

        // 3. Cek apakah boleh upload (gunakan method yang sudah ada!)
        if (!$payment->canUploadProof()) {
            session()->flash('error', 'Tidak dapat mengupload bukti transfer untuk payment ini.');
            return;
        }

        // 4. Simpan file ke storage
        //    PENTING: JANGAN hapus file lama (untuk audit trail)
        $path = $this->proofFile->store('payments/proofs', 'public');

        // 5. Update payment
        $payment->update([
            'transfer_proof' => $path,
        ]);

        // 6. Jika status rejected, kembalikan ke pending dan clear rejection_reason
        if ($payment->status === PaymentStatus::Rejected) {
            $payment->update([
                'status' => PaymentStatus::Pending->value,
                'rejection_reason' => null,
            ]);
        }

        // 7. Reset state dan tampilkan notifikasi
        $this->uploadingPaymentId = null;
        $this->proofFile = null;
        unset($this->payments); // Clear computed cache

        session()->flash('success', 'Bukti transfer berhasil diupload.');
    }

    public function cancelPayment(int $paymentId): void
    {
        $payment = Payment::where('id', $paymentId)
            ->where('contingent_id', auth()->user()->contingent->id)
            ->firstOrFail();

        if (!$payment->canBeCancelledByUser()) {
            session()->flash('error', 'Pembayaran ini tidak dapat dibatalkan.');
            return;
        }

        try {
            DB::transaction(function () use ($payment) {
                // 1. Update Payment Status to Cancelled
                $payment->update([
                    'status' => PaymentStatus::Cancelled,
                ]);

                // 2. Soft-delete all associated registrations
                $payment->registrations()->delete();

                // 3. Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'payment.cancelled_by_user',
                    'subject_type' => 'Payment',
                    'subject_id' => $payment->id,
                    'description' => "User membatalkan pendaftaran untuk event: {$payment->event->name}",
                    'properties' => [
                        'payment_id' => $payment->id,
                        'event_name' => $payment->event->name,
                        'total_amount' => $payment->total_amount,
                    ],
                ]);
            });

            unset($this->payments); // Clear computed cache
            session()->flash('success', 'Pendaftaran berhasil dibatalkan.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan pendaftaran: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.payment-list');
    }
}
