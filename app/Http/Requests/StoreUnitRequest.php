<?php

namespace App\Http\Requests;

use App\Models\Unit;
use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Unit::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'unit_type' => ['nullable', Rule::in(['revenue', 'service'])],
            'parent_id' => ['nullable', 'string', 'exists:units,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'head_id' => ['nullable', 'integer', 'exists:employees,id'],
            'deputy_id' => ['nullable', 'integer', 'exists:employees,id'],
            'founded_at' => ['nullable', 'date'],
            'website' => ['nullable', 'string', 'max:255'],
            'stage' => ['nullable', 'string', Rule::in(PultEnums::unitStages())],
            'description' => ['nullable', 'string', 'max:5000'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'inn' => ['nullable', 'string', 'max:50'],
        ];
    }
}
