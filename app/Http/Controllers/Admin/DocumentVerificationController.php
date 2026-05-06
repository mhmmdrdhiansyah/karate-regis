<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ParticipantType;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentVerificationController extends Controller
{
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

        // Urutkan: Belum terverifikasi paling atas
        $query->orderBy('is_verified', 'asc')
              ->latest();

        $participants = $query->paginate(15)->withQueryString();

        return view('admin.document-verification.index', compact('participants'));
    }

    public function approve(Request $request, Participant $participant)
    {
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
                ]);

                // 2. Update SEMUA registrasi miliknya yang sedang 'pending_review' menjadi 'verified'
                Registration::where('participant_id', $participant->id)
                    ->where('status_berkas', RegistrationStatus::PendingReview->value)
                    ->update([
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
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, Participant $participant)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        try {
            DB::transaction(function () use ($request, $participant) {
                // Pastikan participant tetap unverified
                $participant->update([
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                ]);

                // Tolak semua registrasi yang sedang menunggu
                Registration::where('participant_id', $participant->id)
                    ->where('status_berkas', RegistrationStatus::PendingReview->value)
                    ->update([
                        'status_berkas' => RegistrationStatus::Rejected->value,
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
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
