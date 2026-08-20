<?php

namespace App\Http\Requests\Api;

use App\Enums\LeadInterest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:120'],
            'company'        => ['nullable', 'string', 'max:120'],
            'position'       => ['nullable', 'string', 'max:120'],
            'phone'          => ['required', 'string', 'max:20'],
            'email'          => ['required', 'email', 'max:120'],
            'interest'       => ['nullable', Rule::enum(LeadInterest::class)],
            'consent_data'   => ['accepted'],
            'consent_policy' => ['accepted'],
            'consent_news'   => ['nullable', 'boolean'],
            'utm'            => ['nullable', 'array'],
        ];
    }
}
