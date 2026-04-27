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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $this->route('kontingen')->user_id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->route('kontingen')->user_id],
            'official_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah terdaftar',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'official_name.required' => 'Nama official wajib diisi',
        ];
    }
}
