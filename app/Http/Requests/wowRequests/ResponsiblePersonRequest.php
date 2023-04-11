<?php

namespace App\Http\Requests\wowRequests;

use App\Rules\IsResponsiblePersonUnique;
use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class ResponsiblePersonRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                new IsResponsiblePersonUnique($this->name),
            ]
        ];
    }

}
