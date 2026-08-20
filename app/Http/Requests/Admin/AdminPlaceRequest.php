<?php

namespace App\Http\Requests\Admin;

use App\Enums\ModerationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:120'],
            'category_id'       => ['required', 'exists:categories,id'],
            'district_id'       => ['required', 'exists:districts,id'],
            'address'           => ['required', 'string', 'max:255'],
            'lat'               => ['nullable', 'numeric', 'between:-90,90'],
            'lng'               => ['nullable', 'numeric', 'between:-180,180'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'email'             => ['nullable', 'email'],
            'site'              => ['nullable', 'url'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'price_level'       => ['required', 'integer', 'between:1,3'],
            'status'            => ['required', Rule::enum(ModerationStatus::class)],
            'is_featured'       => ['nullable', 'boolean'],
            'socials'           => ['nullable', 'array'],
        ];
    }
}
