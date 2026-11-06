<?php

namespace App\Http\Requests;

use App\Interfaces\HasLikesInterface;
use App\Rules\ModelInterfaceRule;
use App\Rules\MustNotBeOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LikeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));

        $this->merge([
            $morphsAttributeId => $this->get('model_id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));

        return [
            'model_type' => ['required', new ModelInterfaceRule(HasLikesInterface::class)],
            'model_id' => [
                'required',
                Rule::unique('likes', 'likeable_id')
                    ->where('likeable_type', classByString($this->get('model_type')))
                    ->where('created_by', auth()->id()),
            ],
            $morphsAttributeId => new MustNotBeOwner(getMorphTable($this->get('model_type'))),
        ];
    }

    public function getModel(): Model
    {
        return morphedModel($this->get('model_type'), $this->get('model_id'));
    }
}
