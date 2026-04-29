<?php

namespace App\Http\Requests\Participant;

use App\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParticipantRequest extends FormRequest
{
    private ?Participant $participant;

    public function __construct()
    {
        $this->participant = $this->route('participant');
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $participantId = $this->participant?->id;

        return [
            'type' => 'required|in:athlete,coach,official',
            'name' => 'required|string|max:255',
            'nik' => ['required', 'digits:16', 'unique:participants,nik,' . $participantId],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:M,F'],
            'provinsi' => 'nullable|string|max:255',
            'institusi' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis peserta wajib diisi',
            'type.in' => 'Jenis peserta tidak valid',
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'nik.required' => 'NIK wajib diisi',
            'nik.digits' => 'NIK harus 16 digit angka',
            'nik.unique' => 'NIK sudah terdaftar',
            'birth_date.required' => 'Tanggal lahir wajib diisi',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini',
            'gender.required' => 'Gender wajib diisi',
            'gender.in' => 'Gender tidak valid',
            'photo.image' => 'File harus berupa gambar',
            'photo.max' => 'Ukuran foto maksimal 2MB',
            'document.mimes' => 'Dokumen harus berupa JPG, PNG, atau PDF',
            'document.max' => 'Ukuran dokumen maksimal 5MB',
        ];
    }
}
