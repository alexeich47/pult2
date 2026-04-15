<?php

namespace App\Http\Requests;

use App\Models\RiskEntry;
use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRiskEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', RiskEntry::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->input('type');
        $allowedStatuses = RiskEntry::STATUSES_BY_TYPE[$type] ?? [];

        return [
            'type' => ['required', Rule::in(PultEnums::riskTypes())],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'owner_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in($allowedStatuses)],
            'entry_date' => ['required', 'date'],
        ];
    }
}
