<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContingentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit own kontingen');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'official_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kontingen wajib diisi',
            'name.max' => 'Nama kontingen maksimal 255 karakter',
            'official_name.required' => 'Nama official wajib diisi',
            'official_name.max' => 'Nama official maksimal 255 karakter',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',
        ];
    }
}
