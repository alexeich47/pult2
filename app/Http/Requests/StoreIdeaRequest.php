<?php

namespace App\Http\Requests;

use App\Models\Idea;
use App\Support\PultEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Idea::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'string', 'exists:units,id'],
            'author_id' => ['required', 'integer', 'exists:employees,id'],
            'priority' => ['required', Rule::in(PultEnums::ideaPriorities())],
            'status' => ['required', Rule::in(PultEnums::ideaStatuses())],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'impact' => ['required', 'string', 'max:5000'],
        ];
    }
}
