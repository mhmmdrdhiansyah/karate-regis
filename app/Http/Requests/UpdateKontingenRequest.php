<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKontingenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit kontingen');
    }

    public function rules(): array
    {
        return [
            'contingent_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $this->route('kontingen')->user_id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->route('kontingen')->user_id],
            'official_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'province' => ['required', 'string', 'max:255'],
            'regency' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'contingent_name.required' => 'Nama kontingen wajib diisi',
            'contingent_name.max' => 'Nama kontingen maksimal 255 karakter',
            'name.required' => 'Nama lengkap wajib diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah terdaftar',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'official_name.required' => 'Nama official wajib diisi',
            'province.required' => 'Provinsi wajib dipilih',
            'regency.required' => 'Kabupaten/Kota wajib dipilih',
        ];
    }
}
