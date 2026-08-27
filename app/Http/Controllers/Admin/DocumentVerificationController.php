<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ParticipantType;
use App\Enums\RegistrationStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\RejectDocumentRequest;

class DocumentVerificationController extends Controller
{
    /**
     * Tolak aksi tulis bila panitia tidak mengelola event peserta ini,
     * atau bila event peserta sudah completed (read-only).
     */
    private function guardScoped(Participant $participant): void
    {
        if (auth()->user()->hasRole('super-admin')) {
            return;
        }

        $registrations = $participant->registrations()
            ->forManagedEvents()
            ->with('payment.event')
            ->get();

        abort_if($registrations->isEmpty(), 403, 'Peserta tidak ada di event yang ditugaskan.');

        abort_if(
            $registrations->contains(fn ($r) => $r->payment?->event?->status === EventStatus::Completed),
            403,
            'Event selesai, data read-only.'
        );
    }

    public function index(Request $request)
    {
        $query = Participant::with('contingent')
            ->where('type', ParticipantType::Athlete);

        // Filter status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'unverified') {
                $query->where('is_verified', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhereHas('contingent', function ($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%")
                        ->orWhere('official_name', 'like', "%{$search}%");
                  });
            });
        }

        // Panitia hanya melihat peserta yang mendaftar event yang dipegangnya
        if (auth()->user()->hasRole('panitia')) {
            $query->whereHas('registrations', fn ($r) => $r->forManagedEvents());
        }

        // Urutkan: Belum terverifikasi paling atas
        $query->orderBy('is_verified', 'asc')
              ->latest();

        $participants = $query->paginate(15)->withQueryString();

        return view('admin.document-verification.index', compact('participants'));
    }

    public function approve(Request $request, Participant $participant)
    {
        $this->guardScoped($participant);

        if ($participant->is_verified) {
            return response()->json(['message' => 'Peserta ini sudah diverifikasi sebelumnya.'], 400);
        }

        try {
            DB::transaction(function () use ($participant) {
                // 1. Update Participant
                $participant->update([
                    'is_verified' => true,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'rejection_reason' => null,
                ]);

                // 2. Update Registrations
                Registration::forManagedEvents()->where('participant_id', $participant->id)->update([
                    'status_berkas' => RegistrationStatus::Verified->value,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'rejection_reason' => null,
                ]);

                // 3. Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'participant.verified',
                    'subject_type' => 'Participant',
                    'subject_id' => $participant->id,
                    'description' => "Admin memverifikasi dokumen akta/ijazah atlet: {$participant->name}",
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Dokumen atlet berhasil diverifikasi.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error approving participant {$participant->id}: " . $e->getMessage(), [
                'exception' => $e,
                'admin_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memverifikasi dokumen.'], 500);
        }
    }

    public function reject(RejectDocumentRequest $request, Participant $participant)
    {
        $this->guardScoped($participant);

        try {
            DB::transaction(function () use ($request, $participant) {
                // Pastikan participant unverified & simpan alasan penolakan
                $participant->update([
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => $request->rejection_reason,
                ]);

                // Update Registrations
                Registration::forManagedEvents()->where('participant_id', $participant->id)->update([
                    'status_berkas' => RegistrationStatus::Rejected->value,
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => $request->rejection_reason,
                ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'participant.rejected',
                    'subject_type' => 'Participant',
                    'subject_id' => $participant->id,
                    'description' => "Admin menolak dokumen atlet: {$participant->name}. Alasan: {$request->rejection_reason}",
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Dokumen atlet berhasil ditolak.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error rejecting participant {$participant->id}: " . $e->getMessage(), [
                'exception' => $e,
                'admin_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menolak dokumen.'], 500);
        }
    }

    public function revoke(RejectDocumentRequest $request, Participant $participant)
    {
        $this->guardScoped($participant);

        if (!$participant->is_verified) {
            return response()->json(['message' => 'Hanya peserta yang sudah terverifikasi yang bisa di-revoke.'], 400);
        }

        try {
            DB::transaction(function () use ($request, $participant) {
                // 1. Reset Participant status & simpan alasan revoke
                $participant->update([
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => $request->rejection_reason,
                ]);

                // 2. Reset Registrations status (hanya event yang dipegang panitia)
                Registration::forManagedEvents()->where('participant_id', $participant->id)->update([
                    'status_berkas' => RegistrationStatus::PendingReview->value,
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => $request->rejection_reason,
                ]);

                // 3. Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'participant.revoked',
                    'subject_type' => 'Participant',
                    'subject_id' => $participant->id,
                    'description' => "Admin me-revoke verifikasi atlet: {$participant->name}. Alasan: {$request->rejection_reason}",
                    'properties' => [
                        'participant_id' => $participant->id,
                        'reason' => $request->rejection_reason,
                    ],
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi atlet berhasil dicabut.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error revoking participant {$participant->id}: " . $e->getMessage(), [
                'exception' => $e,
                'admin_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Terjadi kesalahan sistem saat me-revoke verifikasi.'], 500);
        }
    }
}
