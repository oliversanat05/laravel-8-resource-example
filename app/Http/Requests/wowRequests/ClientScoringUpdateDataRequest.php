<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class ClientScoringUpdateDataRequest extends FormRequest
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
            'client_data.*.client_id' => 'required|exists:clients,id',
            'client_data.*.metric_area_id' => 'required|exists:metric_areas,id',
        ];

    }

    public function messages()
    {
        return [
            'client_data.*.client_id.required' => 'Client ID is required',
            'client_data.*.client_id.exists' => 'Client ID should exist in database',
            'client_data.*.metric_area_id.exists' => 'Metric Area ID should exist in database',
            'client_data.*.metric_area_id.required' => 'Metric Area ID is required!!!',
        ];
    }

}
