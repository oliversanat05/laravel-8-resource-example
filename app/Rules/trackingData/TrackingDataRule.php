<?php

namespace App\Rules\trackingData;

use App\Models\Tracking\TrackingData;
use Carbon\Carbon;
use Config;
use Illuminate\Contracts\Validation\Rule;

class TrackingDataRule implements Rule
{

    public $beginningDate, $endingDate, $activity, $activityId, $trackingId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($beginningDate, $endingDate, $activity, $activityId, $trackingId)
    {
        $this->beginingDate = $beginningDate;
        $this->endingDate = $endingDate;
        $this->activity = $activity;
        $this->activityId = $activityId;
        $this->trackingId = $trackingId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $startDate = Carbon::parse($this->beginingDate)->format(Config::get('constants.dbDateFormat'));
        $endDate = Carbon::parse($this->endingDate)->format(Config::get('constants.dbDateFormat'));

        if( $startDate > $endDate) // begining date must be less than ending data
            return false;

        $checkTrackingDate = TrackingData::where($this->activity . 'Id', $this->activityId)
            ->where(function ($query) {
                $query->when($this->trackingId, function ($query) {
                    $query->whereNotIn('trackingDataId', [$this->trackingId]);
                });
            })
			->where(function ($query) use ($startDate, $endDate) {
				$query->where('startDate', $startDate);
				$query->OrWhere('startDate', $endDate);
				$query->OrWhere('endDate', $startDate);
				$query->OrWhere('endDate', $endDate);
			})
			->exists();

        if ($checkTrackingDate) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('core.dateExist');
    }
}
