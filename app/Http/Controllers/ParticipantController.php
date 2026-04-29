<?php

namespace App\Http\Controllers;

use App\Http\Requests\Participant\StoreParticipantRequest;
use App\Http\Requests\Participant\UpdateParticipantRequest;
use App\Models\Participant;
use App\Services\ParticipantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParticipantController extends Controller
{
    public function __construct(
        private ParticipantService $participantService
    ) {}

    public function index(Request $request)
    {
        $contingent = $request->user()->contingent;

        if (!$contingent) {
            abort(403, 'Akun Anda tidak terkait dengan kontingen.');
        }

        $query = $contingent->participants();

        $type = $request->get('type', 'all');
        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $participants = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('participants.index', compact('participants'));
    }

    public function create()
    {
        return view('participants.create');
    }

    public function store(StoreParticipantRequest $request)
    {
        $validated = $request->validated();
        $contingent = $request->user()->contingent;

        if (!$contingent) {
            abort(403, 'Akun Anda tidak terkait dengan kontingen.');
        }

        $validated['contingent_id'] = $contingent->id;
        $validated['photo'] = $this->participantService->uploadPhoto($request->file('photo'));

        if ($request->hasFile('document')) {
            $validated['document'] = $this->participantService->uploadDocument($request->file('document'));
        }

        Participant::create($validated);

        return redirect()->route('participants.index')->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function show(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        return view('participants.show', compact('participant'));
    }

    public function edit(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $lockedFields = $this->participantService->getLockedFields($participant);
        $canDelete = $this->participantService->canDelete($participant);

        return view('participants.edit', compact('participant', 'lockedFields', 'canDelete'));
    }

    public function update(UpdateParticipantRequest $request, Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $validated = $request->validated();
        $lockedFields = $this->participantService->getLockedFields($participant);

        foreach ($lockedFields as $field) {
            unset($validated[$field]);
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->participantService->uploadPhoto(
                $request->file('photo'),
                $participant
            );
        } else {
            unset($validated['photo']);
        }

        if ($request->hasFile('document') && !in_array('document', $lockedFields)) {
            $validated['document'] = $this->participantService->uploadDocument(
                $request->file('document'),
                $participant
            );
        } else {
            unset($validated['document']);
        }

        $participant->update($validated);

        return redirect()->route('participants.index')->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        if (!$this->participantService->canDelete($participant)) {
            return back()->withErrors([
                'delete' => $participant->is_verified
                    ? 'Peserta yang sudah terverifikasi tidak dapat dihapus.'
                    : 'Peserta memiliki registrasi aktif dan tidak dapat dihapus.',
            ]);
        }

        if ($participant->photo) {
            Storage::disk('public')->delete($participant->photo);
        }

        if ($participant->document) {
            Storage::disk('public')->delete($participant->document);
        }

        $participant->delete();

        return redirect()->route('participants.index')->with('success', 'Peserta berhasil dihapus.');
    }

    private function authorizeParticipant(Participant $participant): void
    {
        abort_unless(
            $participant->contingent_id === request()->user()->contingent?->id,
            403,
            'Anda tidak memiliki akses ke peserta ini.'
        );
    }
}
