<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileClaimRequest extends FormRequest
{
    /**
     * The controller authorizes with the link, which a guest holds.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Keeping a profile asks for what registering does.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'terms_and_conditions' => ['required', Rule::in([true])],
        ];
    }
}
