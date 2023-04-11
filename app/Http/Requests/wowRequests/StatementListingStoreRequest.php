<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use App\Rules\IsStatementDateUnique;
// use App\Rules\IsNotPastDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StatementListingStoreRequest extends FormRequest
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
            'statement_date' => [
                'required',
                'date_format:Y-m-d',
                //  new IsNotPastDate($this->statement_date),
                 new IsStatementDateUnique($this->statement_date),
            ],
            'statement_title' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'statement_date.date_format' => 'Date should be genuine',
            'statement_date.required'=>'Date is required !!',
            'statement_title.required'=>'Statement title is required',
            'statement_title.string'=>'Statement title must be a string',
        ];
    }
}
