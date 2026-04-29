<?php

namespace App\Http\Requests\Admin;

use App\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'name' => ['required', 'string', 'max:255'],
            'event_date' => [
                'required',
                'date',
                $event ? 'date' : 'after_or_equal:today',
            ],
            'registration_deadline' => ['nullable', 'date', 'before:event_date'],
            'coach_fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(EventStatus::class)],
        ];
    }
}
