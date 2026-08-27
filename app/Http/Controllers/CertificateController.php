<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Registration;
use App\Services\CertificateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function __construct(protected CertificateService $service)
    {
    }

    /**
     * Form lookup NIK.
     */
    public function index()
    {
        return view('certificates.index');
    }

    /**
     * Lookup sertifikat via NIK.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit angka.',
        ]);

        $participant = Participant::where('nik', $validated['nik'])->first();

        if (! $participant) {
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.'])->withInput();
        }

        $certificates = $this->service->forParticipant($participant);

        return view('certificates.result', compact('participant', 'certificates'));
    }

    /**
     * Generate PDF sertifikat on-the-fly. Otorisasi: registration harus sah
     * (verified + paid + punya kategori) — dicek ulang via service, bukan
     * hanya route-model binding.
     */
    public function pdf(Registration $registration)
    {
        $entry = $this->service->forParticipant($registration->participant)
            ->firstWhere('registration.id', $registration->id);
        abort_unless($entry, 404);

        $template = $this->service->resolveTemplate($entry['event'], $entry['scope']);
        abort_unless($template, 404, 'Template sertifikat belum tersedia.');

        $imageRealPath = storage_path('app/public/' . $template->image_path);

        $pdf = Pdf::loadView('certificates.pdf', [
            'template' => $template,
            'imageRealPath' => $imageRealPath,
            'replacements' => [
                '{nama}' => $registration->participant->name,
                '{kategori}' => $entry['category'],
                '{kelas}' => $entry['class'],
                '{subkategori}' => $entry['sub_category'],
                '{status}' => $entry['status'],
                '{event}' => $entry['event']->name,
                '{xxx}' => $this->service->sequenceNumber($registration),
                '{kontingen}' => $registration->participant->contingent?->name ?? '',
            ],
        ])->setPaper('a4', $template->orientation);

        return $pdf->download(Str::slug($registration->participant->name) . '-sertifikat.pdf');
    }
}
