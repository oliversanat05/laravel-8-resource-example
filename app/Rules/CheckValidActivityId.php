<?php

namespace App\Rules;

use DB;
use App\Models\Tracking\TrackingData;
use Illuminate\Contracts\Validation\Rule;

class CheckValidActivityId implements Rule {
	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct() {
		//
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value) {
		$checkActivityId = TrackingData::orWhere('kpiId', $value)
        ->orWhere('strategyId', $value)
        ->orWhere('projectId', $value)
        ->orWhere('criticalActivityId', $value)
        ->exists();

		if ($checkActivityId) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
		return __('core.activityIdError');
	}
}
