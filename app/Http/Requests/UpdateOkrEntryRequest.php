<?php

namespace App\Http\Requests;

use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOkrEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('okr')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'type' => ['required', Rule::in(PultEnums::okrTypes())],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'period' => ['required', 'string', 'max:50'],
            'progress' => ['integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(PultEnums::okrStatuses())],
            'parent_id' => ['nullable', 'integer', 'exists:okr_entries,id'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
