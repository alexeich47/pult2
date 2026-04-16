<?php

namespace App\Http\Requests;

use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstructionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('instruction')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(PultEnums::instructionTypes())],
            'content' => ['nullable', 'string'],
            'checklist_items' => ['nullable', 'array'],
            'checklist_items.*.text' => ['required_with:checklist_items', 'string'],
            'checklist_items.*.checked' => ['required_with:checklist_items', 'boolean'],
        ];
    }
}
