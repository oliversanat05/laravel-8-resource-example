<?php

namespace App\Http\Requests\trackingRequest;

use Illuminate\Foundation\Http\FormRequest;

class RevertDeletedTrackingDataRequest extends FormRequest
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
            'id' => ['required', 'exists:autoTrackingDates,id']
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'The Tracking data date is required',
            'id.exists' => 'The Tracking Data Date does not exists'
        ];
    }
}
