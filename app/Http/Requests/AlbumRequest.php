<?php

namespace App\Http\Requests;

use App\Models\Album;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AlbumRequest
 *
 * @property Album $album
 */
class AlbumRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod(Request::METHOD_POST)) {
            return true;
        }

        return $this->album->created_by === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if ($this->isMethod(Request::METHOD_POST)) {
            return [
                'name' => 'required|string|max:255',
                'released' => 'nullable|date',
                'description' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'string',
                'image' => FileService::getImageRules(),
            ];
        } else {
            return [
                'name' => 'string|max:255',
                'released' => 'nullable|date',
                'description' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'string',
                'image' => FileService::getImageRules(),
            ];
        }
    }
}
