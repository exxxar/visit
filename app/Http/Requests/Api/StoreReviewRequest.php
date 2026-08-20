<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // гости тоже могут оставлять отзывы (на модерацию)
    }

    public function rules(): array
    {
        return [
            'rating'      => ['required', 'integer', 'between:1,5'],
            'text'        => ['nullable', 'string', 'max:2000'],
            'author_name' => ['required_without:user_id', 'nullable', 'string', 'max:120'],
        ];
    }
}
