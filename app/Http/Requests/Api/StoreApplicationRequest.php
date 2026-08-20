<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // шаг 1 — организация
            'org_name'         => ['required', 'string', 'max:120'],
            'category'         => ['required', 'string', 'max:60'],
            'district'         => ['nullable', 'string', 'max:60'],
            'address'          => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:120'],
            'site'             => ['nullable', 'url', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            // шаг 2 — контактное лицо
            'contact_name'     => ['required', 'string', 'max:120'],
            'contact_position' => ['nullable', 'string', 'max:120'],
            'contact_phone'    => ['required', 'string', 'max:20'],
            'contact_email'    => ['nullable', 'email', 'max:120'],
            // шаг 3 — медиа
            'media'            => ['nullable', 'array', 'max:10'],
            'socials'          => ['nullable', 'array'],
        ];
    }
}
