<?php

namespace App\Http\Requests;

use App\Enums\FileTypes;
use App\Enums\ProductTypes;
use App\Interfaces\HasProductInterface;
use App\Models\Track;
use App\Models\User;
use App\Rules\DecimalRule;
use App\Rules\ModelInterfaceRule;
use App\Rules\MustBeOwner;
use App\Services\FileService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Request;

class ProductRequest extends FormRequest
{
    private ?HasProductInterface $orderable;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @throws AuthorizationException
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isAccountReady()) {
            throw new AuthorizationException(trans('exceptions.invalid_account'));
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));

        $this->orderable = morphedModel($this->get('model_type'), $this->get('model_id'));

        $this->merge([
            $morphsAttributeId => $this->get('model_id'),
            'already_selling' => $this->orderable?->product()?->exists(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $fileRule = ['nullable', 'file', 'max:10000'];

        if ($this->orderable instanceof Track) { // TODO: try to refactor this
            $fileRule[] = 'mimetypes:'.implode(',', FileService::$AUDIO_MIMES);
        } else {
            $fileRule[] = 'mimetypes:'.implode(',', FileService::$IMAGE_MIMES);
        }

        if ($this->isMethod(Request::METHOD_POST)) {
            $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));

            return [
                $morphsAttributeId => new MustBeOwner(getMorphTable($this->get('model_type'))),
                'already_selling' => Rule::in([false]),

                'model_id' => 'required',
                'model_type' => ['required', new ModelInterfaceRule(HasProductInterface::class)],
                'product_type' => ['nullable', new Enum(ProductTypes::class)],
                'price' => ['required', new DecimalRule],
                'exclusive' => ['nullable', 'boolean'],

                'file' => $fileRule,
                'file_type' => ['nullable', new Enum(FileTypes::class)],
            ];
        } else {
            return [
                'name' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['nullable', new DecimalRule],
                'exclusive' => ['nullable', 'boolean'],

                'file' => $fileRule,
                'file_type' => ['nullable', new Enum(FileTypes::class)],
            ];
        }
    }

    public function messages(): array
    {
        return [
            'already_selling.in' => trans('validation.already_selling'),
        ];
    }
}
