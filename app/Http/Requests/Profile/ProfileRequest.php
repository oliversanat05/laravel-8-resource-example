<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'profile.timeZoneId' => ['required', 'exists:timezones,timeZoneId'],
            'profile.firstName' => ['required'],
            'profile.dateOfBirth' => ['required', 'date_format:Y-m-d']
        ];
    }

    /**
     * customizing the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'profile.timeZoneId.required' => 'The timezone is required',
            'profile.timeZoneId.exists' => 'Timezone does not exist',
            'profile.firstName.required' => 'The first name is required',
            'profile.dateOfBirth.required' => 'The date of birth is required'
        ];
    }
}
