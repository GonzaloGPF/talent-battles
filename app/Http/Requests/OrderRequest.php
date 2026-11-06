<?php

namespace App\Http\Requests;

use App\Interfaces\HasOrderLineInterface;
use App\Models\Cart;
use App\Rules\StripePaymentIntentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public ?HasOrderLineInterface $model;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var Cart $cart */
        $cart = Cart::first();

        $this->merge([
            'items' => $cart->items?->toArray() ?? [],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'items' => 'array|min:1',
            'email' => Rule::requiredIf(auth()->guest()),
            'payment_intent_id' => ['required', 'string', 'max:100', new StripePaymentIntentRule],
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
