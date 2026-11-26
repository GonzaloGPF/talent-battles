<?php

namespace App\Http\Requests;

use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFilesInterface;
use App\Models\User;
use App\Rules\ModelInterfaceRule;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FileRequest extends FormRequest
{
    /**
     * Adding files needs a user who may add them to the model. Changing an existing file
     * is checked by FileController against the FilePolicy.
     */
    public function authorize(): bool
    {
        if (! $this->isMethod(self::METHOD_POST)) {
            return true;
        }

        $user = $this->user();
        $model = $this->model();

        return $user instanceof User && (! $model || $this->canAddFilesTo($user, $model));
    }

    /**
     * The model that the files are added to.
     */
    public function model(): (HasFilesInterface&Model)|null
    {
        $model = morphedModel($this->get('model_type'), $this->get('model_id'));

        return $model instanceof HasFilesInterface ? $model : null;
    }

    /**
     * Only an admin, or the owner of the model, may add files to it: a newer image or audio replaces the one it shows.
     */
    private function canAddFilesTo(User $user, Model $model): bool
    {
        return hasRole(Roles::ADMIN, user: $user)
            || ($model instanceof User && $model->is($user))
            || ($model instanceof HasCreatedByInterface && $model->getAttribute('created_by') === $user->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
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
