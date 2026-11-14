<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\ProfileClaimService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileClaimRequest extends FormRequest
{
    /**
     * Whoever holds a link that still matches the profile decides what happens to it.
     */
    public function authorize(ProfileClaimService $profileClaimService): bool
    {
        /** @var User $user */
        $user = $this->route('user');

        return $profileClaimService->isValidKey($user, $this->query('key'));
    }

    /**
     * Declining needs no data; claiming asks for what registering does.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('delete')) {
            return [];
        }

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
