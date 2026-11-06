<?php

namespace App\Http\Requests;

use App\Enums\FileTypes;
use App\Interfaces\HasFilesInterface;
use App\Rules\ModelInterfaceRule;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FileRequest extends FormRequest
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
        if ($this->get('file_type') === FileTypes::AUDIO->value) {
            $fileRule = FileService::getAudioRules();
        } else {
            $fileRule = FileService::getImageRules();
        }

        if ($this->isMethod(self::METHOD_POST)) {
            return [
                'model_id' => 'required',
                'model_type' => ['required', new ModelInterfaceRule(HasFilesInterface::class)],
                'file_type' => ['nullable', new Enum(FileTypes::class)],
                'file' => $fileRule,
                'files' => 'nullable|array',
                'files.*' => $fileRule,
            ];
        } else {
            return [
                'file' => $fileRule,
                'file_type' => [new Enum(FileTypes::class)],
            ];
        }
    }
}
