<?php

namespace App\Http\Requests\Citizen\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('citizen') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')->where(fn ($query) => $query->whereIn('level', ['province', 'regency', 'district']))],
        ];
    }
}
