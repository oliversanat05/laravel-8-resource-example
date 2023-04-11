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

class StatementListingCopyRequest extends FormRequest
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
            'new_statement_date' => [
                'required',
                'date_format:Y-m-d',
                //  new IsNotPastDate($this->new_statement_date),
                 new IsStatementDateUnique($this->new_statement_date),
            ],
            'listing_id' => 'required|exists:statement_listings,id'
        ];
    }

    public function messages()
    {
        return [
            'new_statement_date.date_format' => 'Date should be genuine',
            'new_statement_date.required'=>'Date is required !!',
        ];
    }
}
