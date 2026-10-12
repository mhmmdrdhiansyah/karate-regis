<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Services\IdCardImageService;
use App\Services\IdCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class IdCardController extends Controller
{
    public function __construct(protected IdCardService $service)
    {
    }

    /**
     * Form lookup publik.
     */
    public function index()
    {
        return view('id-cards.index');
    }

    /**
     * Lookup via NIK.
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

        return $this->resultOrCandidates($participant);
    }

    /**
     * Lookup via nama + tanggal lahir (exact, case-insensitive).
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
            return back()->withErrors(['nama' => 'Nama dan tanggal lahir tidak cocok dengan data peserta mana pun.'])->withInput();
        }

        if ($participants->count() > 1) {
            return view('id-cards.candidates', [
                'nama' => $validated['nama'],
                'candidates' => $participants->map(fn (Participant $participant) => [
                    'participant' => $participant,
                    'url' => URL::temporarySignedRoute('id-cards.show', now()->addMinutes(10), ['participant' => $participant->id]),
                ]),
            ]);
        }

        return $this->resultOrCandidates($participants->first());
    }

    /**
     * Halaman hasil dari klik kandidat, hanya bisa via signed URL.
     */
    public function show(Participant $participant)
    {
        return $this->resultOrCandidates($participant);
    }

    /**
     * PDF on-the-fly: /id-cards/{participant}/{event}/pdf.
     */
    public function pdf(Participant $participant, Event $event)
    {
        $cards = $this->service->forParticipant($participant);
        $card = $cards->firstWhere('event.id', $event->id);
        abort_unless($card, 404);

        $template = $this->service->resolveTemplate($card['event']);
        abort_unless($template, 404, 'Template ID card belum tersedia.');

        $imageRealPath = storage_path('app/public/' . $template->image_path);

        // Orientasi kartu mengikuti gambar template tersimpan
        $dims = app(IdCardImageService::class)->dimensionsOf($template->image_path);
        // mm CR80: sisi panjang 85.6, sisi pendek 54 — ikut orientasi file
        $cardW = $dims['portrait'] ? 54.0 : 85.6;
        $cardH = $dims['portrait'] ? 85.6 : 54.0;

        $photoPath = $participant->photo;
        $photoRealPath = null;
        if (is_string($photoPath) && $photoPath !== '' && Storage::disk('public')->exists($photoPath)) {
            $photoRealPath = storage_path('app/public/' . $photoPath);
        }

        $pdf = Pdf::loadView('id-cards.pdf', [
            'template' => $template,
            'imageRealPath' => $imageRealPath,
            'photoRealPath' => $photoRealPath,
            'participant' => $participant,
            'card' => $card,
            'cardW' => $cardW,
            'cardH' => $cardH,
        ])->setPaper([0, 0, $cardW * 2.8346, $cardH * 2.8346]);

        return $pdf->download(Str::slug($participant->name) . '-id-card.pdf');
    }

    /**
     * 1 peserta → halaman daftar kartu (per event).
     */
    private function resultOrCandidates(Participant $participant)
    {
        $cards = $this->service->forParticipant($participant);

        return view('id-cards.result', [
            'participant' => $participant,
            'cards' => $cards,
        ]);
    }
}
