<?php

namespace App\Rules;

use App\Enums\ProductTypes;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule as ValidationRule;

class ItemDataRule implements Rule
{
    private string $messageText;

    private ?\Illuminate\Validation\Validator $validator;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (! is_array($value)) {
            $this->messageText = trans('validation.invalid', ['attribute' => ta($attribute)]);

            return false;
        }

        $productId = $value['product_id'] ?? '';
        /** @var Product $product */
        $product = Product::find($productId);

        if (empty($product)) {
            $this->messageText = trans('exceptions.not_found_data', ['data' => modelTitle('product')." [id: $productId]"]);

            return false;
        }

        $this->validator = $this->getValidator($product, $value);

        if (! $this->validator) {
            $this->messageText = trans('exceptions.not_found_data', ['data' => tConstValue('product_type', $product->product_type->value)]);

            return false;
        }

        if ($this->validator->fails()) {
            $this->messageText = trans('validation.invalid_product', [
                'type' => tConstValue('product_type', $product->product_type->value),
                'attribute' => $attribute,
                'messages' => collect($this->validator->errors()->messages())
                    ->flatten()
                    ->join(', '),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->messageText;
    }

    private function getValidator(Product $product, array $data): ?\Illuminate\Validation\Validator
    {
        $validator = null;
        $productType = $product->product_type;

        if ($productType === ProductTypes::POINTS) {
            $validator = Validator::make($data, [
                'quantity' => ['nullable', 'numeric', 'min:50', new DecimalRule],
            ]);
        }

        if ($productType === ProductTypes::PROMO) {
            /** @var Product $product */
            $product = morphedModel($data['orderable_type'], $data['orderable_id']);

            $validator = Validator::make($data, [
                'quantity' => ['nullable', 'numeric', 'min:1'],
                'orderable_id' => ['required', new MustBeOwner($product->getTable())],
                'orderable_type' => ['required', 'string', 'max:255', ValidationRule::in([Product::class])],
            ]);
        }

        if (in_array($productType, [ProductTypes::TRACK, ProductTypes::DESIGN])) {
            /** @var User $user */
            $user = auth()->user();

            $validator = Validator::make([
                'product_id' => $product->id,
                'is_sold' => $product?->exclusive && $product?->last_sale,
                'has_file' => $product?->file()->exists(),
                'in_sale' => ! empty($product->price),
                'already_ordered' => $user?->orderLines()
                    ->where('product_id', $product->id)
                    ->where('orderable_id', $product->id)
                    ->where('orderable_type', get_class($product))
                    ->exists(),
            ], [
                'product_id' => ['required', new MustNotBeOwner(Str::plural($productType->value))],
                'is_sold' => ValidationRule::in([false]),
                'has_file' => ValidationRule::in([true]),
                'in_sale' => ValidationRule::in([true]),
                'already_ordered' => ValidationRule::in([false]),
            ], [
                'is_sold.in' => trans('validation.already_sold'),
                'has_file.in' => trans('validation.without_data', ['data' => modelTitle('file')])." ({$product?->name})",
                'in_sale.in' => trans('validation.not_in_sale'),
                'already_ordered.in' => trans('validation.already_ordered'),
            ]);
        }

        if ($productType === ProductTypes::DONATION) {
            $validator = Validator::make($data, [
                'quantity' => ['nullable', 'numeric', 'min:1', new DecimalRule],
                'description' => 'required|string|max:255',
            ]);
        }

        return $validator;
    }
}
