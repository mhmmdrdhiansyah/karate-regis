<?php

namespace App\Http\Requests\Admin;

use App\Enums\SubCategoryGender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_type' => ['required', Rule::in(['individu', 'beregu'])],
            'gender' => ['required', Rule::enum(SubCategoryGender::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['required', 'integer', 'gte:min_participants'],
            'max_teams' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
