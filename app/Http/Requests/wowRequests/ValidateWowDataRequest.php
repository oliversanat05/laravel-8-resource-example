<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class ValidateWowDataRequest extends FormRequest
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
            'wow_tracker.*.client_id' => 'required|exists:clients,id',
            'wow_tracker.*.idea_id' => 'required|exists:ideas,id',
            'wow_tracker.*.tracker_id' => 'required|exists:wow_tracker_headings,id',
        ];
    }

    public function messages()
    {
        return [
            'wow_tracker.*.client_id.required' => 'Client ID is required',
            'wow_tracker.*.client_id.exists' => 'Client ID should exist in database',
            'wow_tracker.*.idea_id.exists' => 'Idea ID should exist in database',
            'wow_tracker.*.idea_id.required' => 'Idea ID is required!!!',
            'wow_tracker.*.tracker_id.exists' => 'Tracker Heading ID should exist in database',
            'wow_tracker.*.tracker_id.required' => 'Tracker Heading ID is required!!!'
        ];
    }
}
