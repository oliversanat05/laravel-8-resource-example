<?php

namespace App\Services\NotificationService;

use App\Models\Profile\CommunicationOption;

class DialsDataService
{

    public function __construct()
    {
        $this->communication = new CommunicationOption();
    }

    public function getDialsContentManage($dialsData)
    {
        $dataArray = array();

        if (isset($dialsData) && !empty($dialsData)) {
            foreach ($dialsData as $keys => $values) {

                if (is_array($values) && !empty($values)) {
                    foreach ($values as $key => $value) {

                        foreach ($value as $index => $val) {

                            $dataArray[$index][] = $val;
                        }
                    }
                }
            }
        }

        return $dataArray;
    }

    /**
     * This function will send notifications to users
     * @param NA
     * @return object
     */
    public function manageHierarchy($data)
    {
        $dataArray = array();
        if (count($data)) {

            foreach ($data as $keys => $values) {

                foreach ($values as $key => $vals) {

                    foreach ($vals as $index => $val) {

                        foreach ($val as $idx => $out) {

                            $dataArray[$idx][$out['id'] . '-' . $out['type']] = $out;
                        }
                    }
                }
            }

        }
        return $dataArray;
    }

    /**
     * this function will get the delegate data
     *
     * @param $delegateName
     * @return array
     */
    public function getDelegateData($delegateName)
    {

        $userData = array();
        $response = $this->communication->getAlertData($delegateName);

        if (count($response)) {

            foreach ($response as $rows) {

                $userData[] = $rows['userId'];
            }
        }
        return $userData;
    }

    /**
     * This function will get goal values according to activity type
     * @param $getDateRangeVal
     * @return object
     */
    public function getGoalValueUsingDates($getDateRangeVal)
    {
        $startDate = isset($getDateRangeVal['assignDate']) ? $getDateRangeVal['assignDate'] : date("Y-01-01");
        $endDate = isset($getDateRangeVal['dueDate']) ? $getDateRangeVal['dueDate'] : date("Y-12-31");

        if ($getDateRangeVal['assignDate'] == 'NULL' || $getDateRangeVal['assignDate'] == '') {
            $startDate = date("Y-01-01");
        }

        if ($getDateRangeVal['dueDate'] == 'NULL' || $getDateRangeVal['dueDate'] == '') {
            $endDate = date("Y-12-31");
        }

        $seasonal = 0;
        $ts1 = strtotime($startDate);
        $ts2 = strtotime($endDate);
        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        $ats1 = strtotime($getDateRangeVal['actualAssignDate']);
        $ats2 = strtotime($getDateRangeVal['actualDueDate']);

        $ayear1 = date('Y', $ats1);
        $ayear2 = date('Y', $ats2);

        $amonth1 = date('m', $ats1);
        $amonth2 = date('m', $ats2);

        $dayofYear = date('z', $ts2);
        $date1 = date_create($startDate);
        $date2 = date_create($endDate);
        $dateDiff = date_diff($date1, $date2);
        $dayofYear = $dateDiff->days ? $dateDiff->days : 1;

        $adate1 = date_create($getDateRangeVal['actualAssignDate']);
        $adate2 = date_create($getDateRangeVal['actualDueDate']);
        $adateDiff = date_diff($adate1, $adate2);
        $actDayofYear = $adateDiff->days ? $adateDiff->days : 1;
        $actDayofYear = $actDayofYear ? $actDayofYear : 1;

        $startMon = 0;
        $nexYrVal = 0;
        $diff = round((($year2 - $year1) * 12) + ($month2 - $month1) + 1);
        $actDiff = round((($ayear2 - $ayear1) * 12) + ($amonth2 - $amonth1) + 1);

        // If goal type is seasonal.
        if (isset($getDateRangeVal['trackSign']) && $getDateRangeVal['trackSign'] == '@') {
            $seasonalGoalarr = explode(',', $getDateRangeVal['seasonalGoal']);

            // if assign month is greater then due month because of next year
            if ($month1 > $month2) {
                for ($startMon = $month1; $startMon <= 12; $startMon++) {
                    if ($getDateRangeVal['accumulate'] == 1) {
                        $seasonal = isset($seasonalGoalarr[$startMon - 1]) ? trim($seasonalGoalarr[$startMon - 1]) : 0;
                    } else {
                        $seasonal += isset($seasonalGoalarr[$startMon - 1]) ? trim($seasonalGoalarr[$startMon - 1]) : 0;
                    }

                }
                for ($nexYrVal = 1; $nexYrVal <= $month2; $nexYrVal++) {
                    if ($getDateRangeVal['accumulate'] == 1) {
                        $seasonal = isset($seasonalGoalarr[$nexYrVal - 1]) ? trim($seasonalGoalarr[$nexYrVal - 1]) : 0;
                    } else {
                        $seasonal += isset($seasonalGoalarr[$nexYrVal - 1]) ? trim($seasonalGoalarr[$nexYrVal - 1]) : 0;
                    }

                }
            } else {
                foreach ($seasonalGoalarr as $key => $value) {
                    # code...
                    if ($key >= $month1 - 1 && $key <= $month2 - 1) {
                        if (isset($value) && $value != '') {
                            if ($getDateRangeVal['accumulate'] == 1) {
                                $seasonal = isset($value) ? trim($value) : 0;
                            } else {
                                $seasonal += isset($value) ? trim($value) : 0;
                            }

                        }
                    }
                }
            }
            return $seasonal;

        } else {
            if ($getDateRangeVal['accumulate'] == 1) {
                return $getDateRangeVal['goal'];
            } else {
                if ($getDateRangeVal['goal'] != '' && $diff != 0) {

                    if (cal_days_in_month(1, $month2, $year2) == date('d', $ts2) && 1 == date('d', $ts1)) {
                        return $getDateRangeVal['goal'] / ($actDiff / $diff);
                    } else {
                        $calGoal = $getDateRangeVal['goal'] / $actDayofYear;
                        return $calGoal * ($dayofYear);
                    }
                } else {
                    return 0;
                }

            }
        }
    }
}
