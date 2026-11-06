<?php

namespace App\Http\Requests;

use App\Enums\ReportStates;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasReportsInterface;
use App\Models\Report;
use App\Rules\ModelInterfaceRule;
use App\Rules\MustNotBeOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
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
            'already_reported' => Report::query()
                ->where('reportable_id', $this->get('model_id'))
                ->where('reportable_type', classByString($this->get('model_type')))
                ->where('created_by', auth()->id())
                ->whereIn('report_state', ReportStates::getPendingStates())
                ->exists(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));
        $morphRule = $this->getMorphRule();

        return [
            'already_reported' => Rule::in([false]),
            'message' => 'required|string',
            'model_type' => ['nullable', new ModelInterfaceRule(HasReportsInterface::class)],
            $morphsAttributeId => $morphRule,
        ];
    }

    public function messages(): array
    {
        return [
            'already_reported.in' => trans('validation.already_reported'),
        ];
    }

    public function getModel(): ?Model
    {
        return morphedModel($this->get('model_type'), $this->get('model_id'));
    }

    private function getMorphRule(): array
    {
        $morphRule = ['nullable'];
        $model = morphedModel($this->get('model_type'), $this->get('model_id'));

        if (! $model) {
            return $morphRule;
        }
        if (implementsInterface(get_class($model), HasCreatedByInterface::class)) {
            $morphRule[] = new MustNotBeOwner;
        }

        return $morphRule;
    }
}
