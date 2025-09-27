<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocaleSwitchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Переключение языка доступно всем
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'locale' => 'required|string|in:' . implode(',', array_keys(config('app.available_locales')))
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'locale.required' => 'Locale is required',
            'locale.in' => 'Unsupported locale. Available: ' . implode(', ', array_keys(config('app.available_locales')))
        ];
    }
}
