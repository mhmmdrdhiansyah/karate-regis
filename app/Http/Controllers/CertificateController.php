<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Registration;
use App\Services\CertificateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
     * Lookup sertifikat via nama + tanggal lahir (exact match, case-insensitive).
     */
    public function lookupByName(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'min:3', 'max:100', 'regex:/^[\pL\s.\',\-]+$/u'],
            'birth_date' => ['required', 'date', 'before:today'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.min' => 'Nama minimal 3 karakter.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nama.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, koma, apostrof, dan tanda hubung.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        $participants = Participant::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['nama'])])
            ->whereDate('birth_date', $validated['birth_date'])
            ->get();

        if ($participants->isEmpty()) {
            return back()
                ->withErrors(['nama' => 'Nama dan tanggal lahir tidak cocok dengan data peserta mana pun.'])
                ->withInput();
        }

        if ($participants->count() > 1) {
            $candidates = $participants->map(fn (Participant $participant) => [
                'participant' => $participant,
                'url' => URL::temporarySignedRoute(
                    'certificates.public.show',
                    now()->addMinutes(10),
                    ['participant' => $participant->id],
                ),
            ]);

            return view('certificates.candidates', [
                'nama' => $validated['nama'],
                'candidates' => $candidates,
            ]);
        }

        $participant = $participants->first();
        $certificates = $this->service->forParticipant($participant);

        return view('certificates.result', compact('participant', 'certificates'));
    }

    /**
     * Halaman hasil dari klik kandidat, hanya bisa via signed URL.
     */
    public function show(Participant $participant)
    {
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
                '{subkategori}' => $registration->subCategory?->full_name ?? $entry['sub_category'],
                '{status}' => $entry['status'],
                '{event}' => $entry['event']->name,
                '{xxx}' => $this->service->sequenceNumber($registration),
                '{kontingen}' => $registration->participant->contingent?->name ?? '',
            ],
        ])->setPaper('a4', $template->orientation);

        return $pdf->download(Str::slug($registration->participant->name) . '-sertifikat.pdf');
    }
}
