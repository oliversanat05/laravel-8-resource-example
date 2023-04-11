<?php

namespace App\Http\Requests\wowRequests;

use App\Rules\IsIdeaUnique;
use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IdeaCreateRequest extends FormRequest
{
    use FailedValidation;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'idea_type' => 'required|in:1,2',
            'idea_title' => [
                'required',
                 new IsIdeaUnique($this->idea_type),
            ],
        ];
    }

}
