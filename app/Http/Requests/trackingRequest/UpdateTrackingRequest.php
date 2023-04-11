<?php

namespace App\Http\Requests\trackingRequest;

use Carbon\Carbon;
use App\Rules\CheckValidActivityId;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\trackingData\TrackingDataRule;

class UpdateTrackingRequest extends FormRequest
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
            'trackingId' => ['required'],
            'comment' => "",
            'activity' => ['required'],
            'beginningDate' => ['required', 'date_format:Y-m-d', new TrackingDataRule($this->beginningDate, $this->endingDate, $this->activity, $this->activityId, $this->trackingId)],
            'endingDate' => ['required', 'date_format:Y-m-d', new TrackingDataRule($this->beginningDate, $this->endingDate, $this->activity, $this->activityId, $this->trackingId)],
        ];
    }

    /**
     * this function will update the tracking data
     *
     * @return array
     */
    public function updateTrackingData()
    {
        return [
            'startDate' => Carbon::parse($this->beginningDate)->format('Y-m-d'),
            'endDate' => Carbon::parse($this->endingDate)->format('Y-m-d'),
            'comment' => $this->comment,
            'trackingValue' => $this->data ? $this->data : 0
        ];
    }
}
