<?php

namespace App\Http\Requests;

use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('meeting')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(PultEnums::meetingTypes())],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(PultEnums::meetingStatuses())],
        ];
    }
}
