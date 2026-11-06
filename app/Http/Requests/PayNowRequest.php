<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class PayNowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @throws AuthorizationException
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user?->hasDefaultPaymentMethod()) {
            throw new AuthorizationException(trans('exceptions.without_data', ['data' => trans('help.default_payment_method')]));
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->merge([
            'items' => $user->cart?->fresh()?->items?->toArray() ?? [],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => 'array|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.min' => trans('validation.min.array', [
                'attribute' => modelTitle('cart'),
                'min' => 1,
            ]),
        ];
    }
}
