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
    ) {
        $this->middleware('permission:view participants|create participants|edit participants')->only(['index', 'show', 'checkNik']);
        $this->middleware('permission:create participants')->only(['create', 'store']);
        $this->middleware('permission:edit participants')->only(['edit', 'update']);
        $this->middleware('permission:delete participants')->only(['destroy', 'deletePreview']);
    }

    public function index(Request $request)
    {
        return view('participants.index');
    }

    public function create()
    {
        $sports = \App\Models\Sport::where('is_active', true)->with('perguruan')->get();
        return view('participants.create', compact('sports'));
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

        $participant = Participant::create($validated);
        $this->participantService->autoVerifyIfNeeded($participant);

        return redirect()->route('participants.index')->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function show(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $participant->load([
            'sport',
            'registrations' => function ($query) {
                $query->with([
                    'payment.event',
                    'subCategory.eventCategory',
                ])->latest();
            },
        ]);

        $canDelete = $this->participantService->canDelete($participant);
        $deleteReason = $this->participantService->getDeleteReason($participant);
        $hasActiveRegistration = $this->participantService->hasActiveRegistration($participant);

        $registrations = $participant->registrations;

        return view('participants.show', compact('participant', 'canDelete', 'deleteReason', 'hasActiveRegistration', 'registrations'));
    }

    public function edit(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $lockedFields = $this->participantService->getLockedFields($participant);
        $canDelete = $this->participantService->canDelete($participant);

        $lockReasons = [];
        foreach ($lockedFields as $field) {
            $lockReasons[$field] = $this->participantService->getLockReason($participant, $field);
        }

        $sports = \App\Models\Sport::where('is_active', true)->with('perguruan')->get();

        return view('participants.edit', compact('participant', 'lockedFields', 'canDelete', 'lockReasons', 'sports'));
    }

    public function update(UpdateParticipantRequest $request, Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $validated = $request->validated();
        $lockedFields = $this->participantService->getLockedFields($participant);

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

        $validated['rejection_reason'] = null;
        $participant->update($validated);

        return redirect()->route('participants.index')->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        $this->participantService->cascadeDelete($participant);

        return redirect()->route('participants.index')->with('success', 'Peserta berhasil dihapus.');
    }

    public function deletePreview(Participant $participant)
    {
        $this->authorizeParticipant($participant);

        return response()->json(
            $this->participantService->getDeleteImpact($participant)
        );
    }

    public function checkNik(Request $request)
    {
        $request->validate([
            'nik' => 'required|digits:16',
            'exclude_id' => 'nullable|integer|exists:participants,id',
        ]);

        $query = Participant::where('nik', $request->nik);

        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'NIK sudah terdaftar' : 'NIK tersedia',
        ]);
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
