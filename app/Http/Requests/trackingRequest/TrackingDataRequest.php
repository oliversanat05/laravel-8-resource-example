<?php

namespace App\Http\Requests\trackingRequest;

use App\Rules\CheckValidActivityId;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\trackingData\TrackingDataRule;

class TrackingDataRequest extends FormRequest
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
     *tracking-data
     * @return array
     */
    public function rules()
    {
        return [
            'activityId' => ['required'],
            'comment' => ['required'],
            'activity' => ['required'],
            'beginingDate' => ['required', 'date_format:Y-m-d', new TrackingDataRule($this->beginingDate, $this->endingDate, $this->activity, $this->activityId, $this->id)],
            'endingDate' => ['required', 'date_format:Y-m-d', new TrackingDataRule($this->beginingDate, $this->endingDate, $this->activity, $this->activityId, $this->id)]
        ];
    }
}
