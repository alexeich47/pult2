<?php

namespace App\Http\Requests;

use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('service')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(PultEnums::serviceCategories())],
            'unit_id' => ['required', 'string', 'exists:units,id'],
            'cost' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(PultEnums::serviceCurrencies())],
            'billing' => ['required', Rule::in(PultEnums::serviceBillingCycles())],
            'next_payment' => ['nullable', 'date'],
            'status' => ['required', Rule::in(PultEnums::serviceStatuses())],
        ];
    }
}
