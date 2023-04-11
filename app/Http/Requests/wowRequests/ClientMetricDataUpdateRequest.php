<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientMetricDataUpdateRequest extends FormRequest
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
            '*.metric_data_id' => 'required|exists:client_metric_data,id'
        ];
    }

    public function messages()
    {
        return [
            '*.metric_data_id.required' => 'Metric data ID field is required',
            '*.metric_data_id.exists' => 'Sorry, one of the metric ID, does not exists in our database',
        ];
    }

}
