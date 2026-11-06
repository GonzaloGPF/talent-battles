<?php

namespace App\Http\Requests;

use App\Rules\ItemDataRule;
use Illuminate\Foundation\Http\FormRequest;

class CartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if (! in_array($this->getMethod(), [self::METHOD_PUT, self::METHOD_PATCH])) {
            return [];
        }

        if ($this->get('deleting')) {
            return [
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'int',
            ];
        } else {
            return [
                'items_data' => 'required|array',
                'items_data.*' => new ItemDataRule,
            ];
        }
    }
}
