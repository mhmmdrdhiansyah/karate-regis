<?php

namespace App\Http\Requests\Admin;

use App\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $event = $this->route('event');
        if ($event) {
            return $user->can('edit events') || $user->can('manage events');
        }

        return $user->can('create events') || $user->can('manage events');
    }

    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'name' => ['required', 'string', 'max:255'],
            'poster' => ['nullable', 'image', 'max:2048'],
            'event_date' => [
                'required',
                'date',
                $event ? 'date' : 'after_or_equal:today',
            ],
            'registration_deadline' => ['nullable', 'date', 'before:event_date'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'event_fee' => ['required', 'numeric', 'min:0'],
            'coach_fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(EventStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama event wajib diisi.',
            'name.string' => 'Nama event harus berupa teks.',
            'name.max' => 'Nama event maksimal 255 karakter.',
            'poster.image' => 'Poster harus berupa gambar.',
            'poster.max' => 'Ukuran poster maksimal 2MB.',
            'event_date.required' => 'Tanggal event wajib diisi.',
            'event_date.date' => 'Tanggal event harus berupa tanggal yang valid.',
            'event_date.after_or_equal' => 'Tanggal event tidak boleh lebih awal dari hari ini.',
            'registration_deadline.date' => 'Batas pendaftaran harus berupa tanggal yang valid.',
            'registration_deadline.before' => 'Batas pendaftaran harus lebih awal dari tanggal event.',
            'bank_name.string' => 'Nama bank harus berupa teks.',
            'bank_name.max' => 'Nama bank maksimal 100 karakter.',
            'account_number.string' => 'No. rekening harus berupa teks.',
            'account_number.max' => 'No. rekening maksimal 50 karakter.',
            'account_holder.string' => 'Atas nama harus berupa teks.',
            'account_holder.max' => 'Atas nama maksimal 150 karakter.',
            'event_fee.required' => 'Biaya event wajib diisi.',
            'event_fee.numeric' => 'Biaya event harus berupa angka.',
            'event_fee.min' => 'Biaya event minimal 0.',
            'coach_fee.required' => 'Biaya coach wajib diisi.',
            'coach_fee.numeric' => 'Biaya coach harus berupa angka.',
            'coach_fee.min' => 'Biaya coach minimal 0.',
            'status.required' => 'Status event wajib dipilih.',
            'status.enum' => 'Status event tidak valid.',
        ];
    }
}
