<?php

namespace App\Http\Requests;

use App\Enums\AspectRatios;
use App\Enums\DesignTypes;
use App\Enums\Styles;
use App\Enums\Thematics;
use App\Rules\DecimalRule;
use App\Rules\EnoughPoints;
use App\Rules\NameRule;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\RequiredIf;

class DesignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $imageRule = FileService::getImageRules();

        if ($this->isMethod(self::METHOD_POST)) {
            return [
                'album_id' => ['nullable', Rule::exists('albums', 'id')->where('created_by', auth()->id())],
                'design_type' => ['nullable', new Enum(DesignTypes::class)],
                'thematic' => ['nullable', new Enum(Thematics::class)],
                'aspect_ratio' => ['nullable', new Enum(AspectRatios::class)],
                'style' => ['nullable', new Enum(Styles::class)],
                'name' => ['required', new NameRule],
                'color' => 'nullable|string|max:8',
                'image' => array_merge(['required'], $imageRule),
                'description' => 'nullable|string',
                'can_challenge' => 'nullable|boolean',
                'tags' => ['nullable', 'array'],
                'tags.*' => 'string',
                'battle' => Rule::when($this->get('battle'), [new EnoughPoints(config('company.points.prices.create_battle'))]),

                // Product
                'sell' => 'nullable|boolean',
                'auto_tag' => 'nullable|boolean',
                'price' => [new RequiredIf((bool) $this->get('sell')), new DecimalRule],
                'file_to_sell' => array_merge([new RequiredIf($this->get('sell') && ! $this->get('auto_tag')), 'nullable'], $imageRule),
                'exclusive' => 'nullable|boolean',
            ];
        }

        return [
            'album_id' => ['nullable', Rule::exists('albums', 'id')->where('created_by', auth()->id())],
            'design_type' => ['nullable', new Enum(DesignTypes::class)],
            'thematic' => ['nullable', new Enum(Thematics::class)],
            'aspect_ratio' => ['nullable', new Enum(AspectRatios::class)],
            'style' => ['nullable', new Enum(Styles::class)],
            'name' => ['string', new NameRule],
            'color' => 'nullable|string|max:8',
            'bpm' => ['nullable', new DecimalRule],
            'image' => array_merge(['nullable'], $imageRule),
            'description' => 'nullable|string',
            'can_challenge' => 'nullable|boolean',
            'tags' => ['nullable', 'array'],
            'tags.*' => 'string',
        ];
    }
}
