<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreKontingenUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create kontingen');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'contingent_name' => ['required', 'string', 'max:255'],
            'official_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'username.required' => 'Username wajib diisi',
            'username.max' => 'Username maksimal 255 karakter',
            'username.unique' => 'Username sudah terdaftar',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'contingent_name.required' => 'Nama kontingen wajib diisi',
            'contingent_name.max' => 'Nama kontingen maksimal 255 karakter',
            'official_name.required' => 'Nama official wajib diisi',
            'official_name.max' => 'Nama official maksimal 255 karakter',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',
        ];
    }
}
