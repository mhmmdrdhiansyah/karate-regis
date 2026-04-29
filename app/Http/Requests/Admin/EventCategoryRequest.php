<?php

namespace App\Http\Requests\Admin;

use App\Enums\EventCategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(EventCategoryType::class)],
            'class_name' => ['required', 'string', 'max:255'],
            'min_birth_date' => ['required', 'date'],
            'max_birth_date' => ['required', 'date', 'after_or_equal:min_birth_date'],
        ];
    }
}
