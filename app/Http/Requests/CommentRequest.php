<?php

namespace App\Http\Requests;

use App\Interfaces\HasCommentsInterface;
use App\Rules\ModelInterfaceRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'comment_id' => 'integer|nullable|exists:comments,id',
                'model_id' => 'required',
                'model_type' => ['required', new ModelInterfaceRule(HasCommentsInterface::class)],
                'content' => 'required|string',
                'files' => 'array',
                'files.*' => 'file',
            ];
        } else {
            return array_merge([
                'comment_id' => 'integer|nullable|exists:comments,id',
                'content' => 'string',
            ], (new FileRequest)->rules());
        }
    }
}
