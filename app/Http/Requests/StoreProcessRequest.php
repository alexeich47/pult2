<?php

namespace App\Http\Requests;

use App\Models\Process;
use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Process::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['nullable', 'string', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'owner_id' => ['nullable', 'integer', 'exists:employees,id'],
            'category' => ['required', Rule::in(PultEnums::processCategories())],
            'maturity' => ['required', Rule::in(PultEnums::processMaturityLevels())],
            'document_url' => ['nullable', 'string', 'max:2048'],
            'tool' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
