<?php

namespace App\Http\Requests\callmaximizerRequest;

use Illuminate\Foundation\Http\FormRequest;
use Config;

class NextCoachingSessionRequest extends FormRequest
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
            'timezone' => ['required', 'exists:timezones,timezoneId'],
            'scheduleTime' => ['required', 'exists:callTime,callTimeId'],
            'nextCallDate' => ['required', 'date_format:'.Config::get('constants.dbDateFormat')]
        ];
    }
}
