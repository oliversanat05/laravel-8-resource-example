<?php

namespace App\Services\NotificationService;


use Carbon\Carbon;

class CheckValidDate {

    /**
     * this function returns the converted date
     *
     * @param $type
     * @param $data
     * @return void
     */
	public function getDateConvert($type, $data) {

		$currentDate = date('Y-m-d');

		$dateRange = $data['dateRange'];

		if (TRIM($type) == 'Past') {
			return date('Y-m-d', strtotime($currentDate . "-" . $dateRange . " days"));
		}

		if (TRIM($type) == 'Current') {
			return date('Y-m-d', strtotime($currentDate));
		}

		if (TRIM($type) == 'Future') {
			return date('Y-m-d', strtotime($currentDate . "+" . $dateRange . " days"));
		}

		if (TRIM($type) == 'All') {
			return true;
		}

	}

    /**
     * this function checks the valid date
     *
     * @param $type
     * @param $preferedDate
     * @param $dueDate
     * @param $startDate
     * @param $endDate
     * @return boolean
     */
	public function is_valid($type, $preferedDate, $dueDate, $startDate, $endDate) {
		$currentDate = date('Y-m-d');

		if (TRIM($type) == 'Past') {
			return (strtotime($preferedDate) <= strtotime($dueDate) && strtotime($currentDate) > strtotime($dueDate)) ? true : false;
		}

		if (TRIM($type) == 'Current') {
			return (strtotime($preferedDate) == strtotime($dueDate)) ? true : false;
		}

		if (TRIM($type) == 'Future') {
			return (strtotime($preferedDate) >= strtotime($dueDate) && strtotime($currentDate) < strtotime($dueDate)) ? true : false;
		}

		if (TRIM($type) == 'All') {
			return true;
		}

		if ($type && $dueDate) {
			$dateRange = $this->getDateRangeToTime($type, $startDate, $endDate);
			if (count($dateRange)) {
				return (strtotime($dueDate) >= $dateRange['start'] && strtotime($dueDate) <= $dateRange['end']) ? true : false;
			} else {
				return false;
			}

		} else {
			return false;
		}

	}

	/**
	 * This function will send notifications to users
	 * @param NA
	 * @return object
	 */
	public function getDateRangeToTime($type, $startDate, $endDate) {

		switch ($type) {

		case 'This Week':

			$previous_week = strtotime("-1 week +1 day");

			$start_week = strtotime("last sunday midnight");
			$end_week = strtotime("next saturday", $start_week);

			$dateRang['start'] = strtotime(date("Y-m-d", $start_week));
			$dateRang['end'] = strtotime(date("Y-m-d", $end_week));
			return $dateRang;

			break;

		case 'This Month':

			$dateRang['start'] = strtotime(date('Y-m-01'));
			$dateRang['end'] = strtotime(date('Y-m-t'));
			return $dateRang;

			break;

		case 'This Year':

			$dateRang['start'] = strtotime(date('Y-01-01'));
			$dateRang['end'] = strtotime(date('Y-12-31'));
			return $dateRang;

			break;

		case 'Last Week':

			$previous_week = strtotime("-1 week +1 day");

			$start_week = strtotime("last sunday midnight", $previous_week);
			$end_week = strtotime("next saturday", $start_week);

			$dateRang['start'] = strtotime(date("Y-m-d", $start_week));
			$dateRang['end'] = strtotime(date("Y-m-d", $end_week));
			return $dateRang;

			break;

		case 'Last Month':

			$dateRang['start'] = strtotime(date('Y-m-d', strtotime('first day of last month')));
			$dateRang['end'] = strtotime(date('Y-m-d', strtotime('last day of last month')));
			return $dateRang;
			break;

		case 'Last Year':

			$dateRang['start'] = strtotime(date('Y-01-01', strtotime('last year')));
			$dateRang['end'] = strtotime(date('Y-12-31', strtotime('last year')));
			return $dateRang;

			break;

		case 'Since 30 days ago':

			$dateRang['start'] = strtotime(date('Y-m-d', strtotime('today - 30 days')));
			$dateRang['end'] = strtotime(date('Y-m-d', strtotime('today')));
			return $dateRang;

			break;
		case 'Since 60 days ago':

			$dateRang['start'] = strtotime(date('Y-m-d', strtotime('today - 60 days')));
			$dateRang['end'] = strtotime(date('Y-m-d', strtotime('today')));
			return $dateRang;

			break;
		case 'Since 90 days ago':

			$dateRang['start'] = strtotime(date('Y-m-d', strtotime('today - 90 days')));
			$dateRang['end'] = strtotime(date('Y-m-d', strtotime('today')));
			return $dateRang;

			break;
		case 'Date by range':

			$dateRang['start'] = strtotime(date('Y-m-d', strtotime($startDate)));
			$dateRang['end'] = strtotime(date('Y-m-d', strtotime($endDate)));
			return $dateRang;

			break;
		}
	}

    /**
     * this function gets the due date of the activity
     *
     * @param $dueDate
     * @return integer
     */
    public function getDueDates($dueDate) {

		$current = date('Y-m-d');
		$datetime1 = date_create($dueDate);
		$datetime2 = date_create($current);

		$interval = date_diff($datetime2, $datetime1);

		$dateDiff = $interval->format('%R%a');
		$dateDiff = intval($dateDiff);

		$userActivityPercentage = 0;
		if ($dateDiff >= 0) {
			$userActivityPercentage = 100;
		} elseif ($dateDiff < 0 && $dateDiff >= -9) {
			$userActivityPercentage = 80;
		} elseif ($dateDiff <= -10 && $dateDiff >= -15) {
			$userActivityPercentage = 60;
		} elseif ($dateDiff <= -16 && $dateDiff >= -20) {
			$userActivityPercentage = 40;
		} elseif ($dateDiff <= -21 && $dateDiff >= -25) {
			$userActivityPercentage = 20;
		} elseif ($dateDiff <= -25) {
			$userActivityPercentage = 0;
		}

		return $userActivityPercentage;
	}

}
