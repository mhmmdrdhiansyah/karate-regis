<?php

namespace App\Modules\AuthManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create permissions');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi',
            'name.unique' => 'Nama permission sudah ada',
            'name.max' => 'Nama permission maksimal 255 karakter',
        ];
    }
}
