<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Http\UploadedFile;
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
            return ['name', 'type', 'nik', 'birth_date', 'gender', 'provinsi', 'institusi', 'document'];
        }

        if ($this->hasActiveRegistration($participant)) {
            return ['nik', 'birth_date', 'gender'];
        }

        return [];
    }

    private function hasActiveRegistration(Participant $participant): bool
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
}
