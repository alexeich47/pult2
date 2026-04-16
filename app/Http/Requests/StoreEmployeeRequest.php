<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Employee::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isVacancy = $this->input('status') === 'vacancy';

        return [
            'unit_id' => ['required', 'string', 'exists:units,id'],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'name' => [$isVacancy ? 'nullable' : 'required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', Rule::in(PultEnums::departments())],
            'email' => ['nullable', 'email', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:64', 'regex:/^@?[A-Za-z0-9_]{3,}$/'],
            'status' => ['required', Rule::in(PultEnums::employeeStatuses())],
            'work_stage' => ['required', Rule::in(PultEnums::workStages())],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('status') === 'vacancy') {
            $this->merge([
                'name' => null,
                'email' => null,
                'telegram' => null,
            ]);
        }
    }
}
