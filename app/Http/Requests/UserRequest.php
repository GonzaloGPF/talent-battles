<?php

namespace App\Http\Requests;

use App\Enums\Categories;
use App\Enums\Gender;
use App\Enums\Roles;
use App\Enums\Skills;
use App\Models\User;
use App\Rules\EnumValue;
use App\Rules\NameRule;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserRequest
 */
class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod(Request::METHOD_DELETE)) {
            return hasRole(Roles::ADMIN);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if ($this->isMethod(Request::METHOD_POST)) {
            return [
                'role' => ['required', new Enum(Roles::class)],
                'name' => ['required', new NameRule],
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => 'nullable|string',
                'image' => FileService::getImageRules(),
                'quote' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'last_name' => 'nullable|string|max:255',
                'nick' => 'nullable|string|max:255',
                'birthdate' => 'nullable|date|before:today',
                'gender' => ['nullable', new Enum(Gender::class)],
                'locale' => 'nullable|string|max:2',
                'skills' => [new EnumValue(Skills::class, true)],
                'categories' => [new EnumValue(Categories::class, true)],
                'address' => 'nullable|array',
                'social_settings' => 'nullable|array',
                'notification_settings' => 'nullable|array',
            ];
        } else {
            /** @var User $user */
            $user = $this->route('user');

            return [
                'role' => ['nullable', new Enum(Roles::class)],
                'name' => ['nullable', new NameRule],
                'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => 'nullable|string',
                'image' => FileService::getImageRules(),
                'quote' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'last_name' => 'nullable|string|max:255',
                'nick' => 'nullable|string|max:255',
                'birthdate' => 'nullable|date|before:today',
                'gender' => ['nullable', new Enum(Gender::class)],
                'locale' => 'nullable|string|max:2',
                'skills' => [new EnumValue(Skills::class, true)],
                'categories' => [new EnumValue(Categories::class, true)],
                'address' => 'nullable|array',
                'social_settings' => 'nullable|array',
                'notification_settings' => 'nullable|array',
            ];
        }
    }
}
