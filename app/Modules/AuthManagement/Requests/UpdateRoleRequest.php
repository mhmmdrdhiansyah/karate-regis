<?php

namespace App\Modules\AuthManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit roles');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $this->role->id],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['exists:permissions,name,guard_name,web'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi',
            'name.unique' => 'Nama role sudah ada',
            'name.max' => 'Nama role maksimal 255 karakter',
            'permissions.required' => 'Minimal pilih 1 permission',
            'permissions.array' => 'Format permission tidak valid',
            'permissions.min' => 'Minimal pilih 1 permission',
            'permissions.*.exists' => 'Permission tidak valid',
        ];
    }
}
