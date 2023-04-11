<?php

namespace App\Http\Requests\callmaximizerRequest;

use Illuminate\Foundation\Http\FormRequest;

class CallMaximizerAssignmentComponent extends FormRequest
{
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
            'id' => ['required', 'exists:callMaximizerData,callMaximizerDataId']
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'The callmaximizer is required.',
            'id.exists' => 'The callmaximizer does not exists.'
        ];
    }
}
