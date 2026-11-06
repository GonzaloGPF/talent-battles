<?php

namespace App\Http\Requests;

use App\Enums\Genres;
use App\Enums\Instruments;
use App\Enums\Thematics;
use App\Enums\TrackTypes;
use App\Rules\DecimalRule;
use App\Rules\EnoughPoints;
use App\Rules\EnumValue;
use App\Rules\NameRule;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\RequiredIf;

class TrackRequest extends FormRequest
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
        $audioRule = FileService::getAudioRules();

        if ($this->isMethod(self::METHOD_POST)) {
            return [
                'album_id' => ['nullable', Rule::exists('albums', 'id')->where('created_by', auth()->id())],
                'track_type' => ['required', new Enum(TrackTypes::class)],
                'thematic' => ['nullable', new Enum(Thematics::class)],
                'instruments' => [new EnumValue(Instruments::class, true), 'max:5'],
                'genres' => [new EnumValue(Genres::class, true), 'max:3'],
                'name' => ['required', new NameRule],
                'color' => 'nullable|string|max:8',
                'bpm' => ['nullable', new DecimalRule],
                'audio' => array_merge(['required'], $audioRule),
                'image' => array_merge(['nullable'], $imageRule),
                'description' => 'nullable|string',
                'can_challenge' => 'nullable|boolean',
                'tags' => ['nullable', 'array'],
                'tags.*' => 'string',
                'battle' => Rule::when($this->get('battle'), [new EnoughPoints(config('company.points.prices.create_battle'))]),

                // Product
                'sell' => 'nullable|boolean',
                'auto_tag' => 'nullable|boolean',
                'price' => [new RequiredIf((bool) $this->get('sell')), new DecimalRule],
                'file_to_sell' => array_merge([new RequiredIf($this->get('sell') && ! $this->get('auto_tag')), 'nullable'], $audioRule),
                'exclusive' => 'nullable|boolean',
            ];
        }

        return [
            'album_id' => ['nullable', Rule::exists('albums', 'id')->where('created_by', auth()->id())],
            'thematic' => ['nullable', new Enum(Thematics::class)],
            'instruments' => [new EnumValue(Instruments::class, true), 'max:5'],
            'genres' => [new EnumValue(Genres::class, true), 'max:3'],
            'name' => ['string', new NameRule],
            'color' => 'nullable|string|max:8',
            'bpm' => ['nullable', new DecimalRule],
            'audio' => array_merge(['nullable'], $audioRule),
            'image' => array_merge(['nullable'], $imageRule),
            'description' => 'nullable|string',
            'can_challenge' => 'nullable|boolean',
            'tags' => ['nullable', 'array'],
            'tags.*' => 'string',
        ];
    }
}
