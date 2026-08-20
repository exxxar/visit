<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'        => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', Rule::exists('districts', 'slug')],
            'category' => ['nullable', 'string', Rule::exists('categories', 'slug')],
            'near'     => ['nullable', 'array'],
            'near.lat' => ['required_with:near', 'numeric', 'between:-90,90'],
            'near.lng' => ['required_with:near', 'numeric', 'between:-180,180'],
            'near.km'  => ['nullable', 'numeric', 'between:0.5,50'],
            'sort'     => ['nullable', Rule::in(['rating', 'new', 'name', 'views'])],
            'per_page' => ['nullable', 'integer', 'between:1,200'], // ← было 50
        ];
    }
}
