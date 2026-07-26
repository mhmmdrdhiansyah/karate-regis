<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationDraftItem;
use App\Models\Result;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParticipantService
{
    public function canEditField(Participant $participant, string $fieldName): bool
    {
        if ($participant->is_verified) {
            return $fieldName === 'photo';
        }

        if ($this->hasActiveRegistration($participant)) {
            return !in_array($fieldName, ['nik', 'birth_date', 'gender']);
        }

        return true;
    }

    public function canDelete(Participant $participant): bool
    {
        if ($participant->is_verified) {
            return false;
        }

        return !$this->hasActiveRegistration($participant);
    }

    public function getLockedFields(Participant $participant): array
    {
        if ($participant->is_verified) {
            return ['name', 'type', 'nik', 'birth_date', 'gender', 'institusi', 'document'];
        }

        if ($this->hasActiveRegistration($participant)) {
            return ['nik', 'birth_date', 'gender'];
        }

        return [];
    }

    public function getLockReason(Participant $participant, string $fieldName): ?string
    {
        if ($participant->is_verified) {
            return 'Field ini terkunci karena data sudah terverifikasi.';
        }

        if ($this->hasActiveRegistration($participant) && in_array($fieldName, ['nik', 'birth_date', 'gender'])) {
            return 'Field ini terkunci karena peserta sudah terdaftar di event.';
        }

        return null;
    }

    public function getDeleteReason(Participant $participant): ?string
    {
        if ($participant->is_verified) {
            return 'Peserta yang sudah terverifikasi tidak dapat dihapus.';
        }

        if ($this->hasActiveRegistration($participant)) {
            return 'Peserta memiliki registrasi aktif dan tidak dapat dihapus.';
        }

        return null;
    }

    public function hasActiveRegistration(Participant $participant): bool
    {
        return $participant->registrations()->whereNull('deleted_at')->exists();
    }

    public function uploadPhoto(UploadedFile $file, ?Participant $participant = null): string
    {
        if ($participant?->photo) {
            Storage::disk('public')->delete($participant->photo);
        }

        return $file->store('participants/photos', 'public');
    }

    public function uploadDocument(UploadedFile $file, ?Participant $participant = null): string
    {
        if ($participant?->document) {
            Storage::disk('public')->delete($participant->document);
        }

        return $file->store('participants/documents', 'public');
    }

    public function deleteFiles(Participant $participant): void
    {
        if ($participant->photo) {
            Storage::disk('public')->delete($participant->photo);
        }

        if ($participant->document) {
            Storage::disk('public')->delete($participant->document);
        }
    }

    /**
     * Soft-deleted registrations and draft items still block a participant's
     * deletion at the DB level (FK restrictOnDelete) — the source of the 500
     * in production. Force-remove every child row in FK order, then delete the
     * participant, all inside a transaction.
     */
    public function cascadeDelete(Participant $participant): void
    {
        DB::transaction(function () use ($participant) {
            $registrationIds = $participant->registrations()
                ->withTrashed()
                ->pluck('id');

            // 1. Results first (FK results.registration_id = restrictOnDelete).
            Result::whereIn('registration_id', $registrationIds)->delete();

            // 2. Force-delete registrations incl. trashed (FK participant_id = restrictOnDelete).
            $participant->registrations()->withTrashed()->forceDelete();

            // 3. Draft items (FK registration_draft_items.participant_id = restrictOnDelete).
            $participant->draftItems()->delete();

            // 4. Uploaded files.
            $this->deleteFiles($participant);

            // 5. The participant itself.
            $participant->delete();
        });
    }

    /**
     * Structured preview of everything cascadeDelete() will remove.
     * Includes soft-deleted registrations so the warning matches reality.
     */
    public function getDeleteImpact(Participant $participant): array
    {
        $registrations = $participant->registrations()
            ->withTrashed()
            ->with(['payment.event', 'subCategory.eventCategory'])
            ->get();

        $results = Result::whereIn('registration_id', $registrations->pluck('id'))
            ->with('registration.payment.event')
            ->get();

        return [
            'participant' => [
                'name' => $participant->name,
                'type' => $participant->type?->value,
            ],
            'counts' => [
                'registrations' => $registrations->count(),
                'results' => $results->count(),
                'draft_items' => $participant->draftItems()->count(),
            ],
            'details' => [
                'registrations' => $registrations->map(fn ($r) => [
                    'event' => $r->payment?->event?->name ?? '-',
                    'category' => $r->subCategory?->eventCategory?->name ?? '-',
                    'status' => $r->status_berkas?->value,
                ])->values()->all(),
                'results' => $results->map(fn ($res) => [
                    'event' => $res->registration?->payment?->event?->name ?? '-',
                    'medal' => $res->medal_type?->value,
                ])->values()->all(),
            ],
        ];
    }

    public function autoVerifyIfNeeded(Participant $participant): void
    {
        if (in_array($participant->type->value, ['coach', 'official'])) {
            $participant->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);
        }
    }
}
