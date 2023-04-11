<?php

namespace App\Http\Requests\wowRequests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidation;

class ClientDataRequest extends FormRequest
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
            'listing_id' => 'required|exists:statement_listings,id',
            'name' => 'required'
        ];
    }
}
