<?php

namespace App\Http\Requests;

use App\Models\RndProject;
use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRndProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', RndProject::class) ?? false;
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
            'owner_id' => ['required', 'integer', 'exists:employees,id'],
            'priority' => ['required', Rule::in(PultEnums::rndPriorities())],
            'status' => ['required', Rule::in(PultEnums::rndStatuses())],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'currency' => ['required', Rule::in(PultEnums::serviceCurrencies())],
            'deadline' => ['nullable', 'date'],
            'success_criteria' => ['required', 'string', 'max:5000'],
            'kill_criteria' => ['required', 'string', 'max:5000'],
            'started_at' => ['nullable', 'date'],
        ];
    }
}
