<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'business']);
    }

    public function rules(): array
    {
        return [
            'place_id' => ['nullable', 'integer', 'exists:places,id'],
            'media'    => ['required', 'file', 'max:51200',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm'],
            'title'    => ['nullable', 'string', 'max:100'],
            'text'     => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required'  => 'Загрузите фото или видео',
            'media.max'       => 'Файл не должен превышать 50 МБ',
            'media.mimetypes' => 'Допустимые форматы: JPG, PNG, WEBP, MP4, WEBM',
        ];
    }
}
