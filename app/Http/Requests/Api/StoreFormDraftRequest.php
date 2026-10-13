<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'draft_reference' => ['nullable', 'string', 'max:40'],
            'form_type' => ['required', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:255'],
            'case_reference' => ['nullable', 'string', 'max:60'],
            'data' => ['nullable', 'array'],
            'current_step' => ['nullable', 'string', 'max:120'],
            'token' => ['nullable', 'string', 'max:120'],
        ];
    }
}
