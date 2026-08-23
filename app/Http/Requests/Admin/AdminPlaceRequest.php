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

            'photos'            => ['nullable', 'array', 'max:10'],
            'photos.*'          => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_photos'     => ['nullable', 'array'],
            'delete_photos.*'   => ['integer', 'exists:place_photos,id'],
            'cover_photo_id'    => ['nullable', 'integer', 'exists:place_photos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.*.max' => 'Каждое фото не более 5 МБ',
            'photos.max'   => 'Не больше 10 фото',
            'photos.*.mimes' => 'Допустимые форматы: JPG, PNG, WEBP',
        ];
    }
}
