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
