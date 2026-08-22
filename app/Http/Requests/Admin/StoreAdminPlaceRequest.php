<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // уже внутри admin.access-группы
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:120'],
            'category_id'       => ['required', 'exists:categories,id'],
            'district_id'       => ['required', 'exists:districts,id'],
            'address'           => ['required', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'email'             => ['nullable', 'email', 'max:120'],
            'site'              => ['nullable', 'string', 'max:255'],
            'price_level'       => ['required', 'integer', 'between:1,3'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'lat'               => ['required', 'numeric', 'between:-90,90'],
            'lng'               => ['required', 'numeric', 'between:-180,180'],
            'is_featured'       => ['boolean'],
            'owner_email'       => ['nullable', 'email', 'exists:users,email'],
            'working_hours'     => ['nullable', 'array'],
            'socials.telegram'  => ['nullable', 'string', 'max:60'],
            'socials.vk'        => ['nullable', 'string', 'max:120'],
            'photos'            => ['nullable', 'array', 'max:10'],
            'photos.*'          => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.*.max' => 'Каждое фото не более 5 МБ',
            'photos.max'   => 'Не больше 10 фото',
        ];
    }
}
