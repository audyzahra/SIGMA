<?php

namespace App\Http\Requests\Officer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('officer') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }
}
