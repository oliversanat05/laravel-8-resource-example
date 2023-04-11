<?php

namespace App\Services\CallMaximizerService;
use App\Models\CallMaximizer\CallMaximizer;
use App\Models\CallMaximizer\CallMaximizerData;
use App\Models\CallMaximizer\CoachCall;
use Auth;
use Carbon\Carbon;
use Config;
use DB;
use Request;

class CallMaximizerService {

	/**
	 * This function will update the next coaching session
	 */
	public function nextCoachingSession($param, $id) {
		$dataArray = [];

		$dataArray['nextTimeZoneId'] = $param['timezone'];
		$dataArray['nextCallTimeId'] = $param['scheduleTime'];
		$dataArray['nextCallDate'] = Carbon::parse($param['nextCallDate'])->format(Config::get('constants.dbDateFormat'));

		$response = CoachCall::where('coachCallId', $id)->update($dataArray);

		return compact('response', 'dataArray');
	}

	/**
	 * This function will create a new call maximizer in
	 *
	 * @param $data
	 * @return bool
	 */
	public function createNewCallMaximizer($data) {

		try {
			DB::beginTransaction();
			$coachCallId = self::addNewCoachCall($data);

			$response = self::callCallMaxData($data);
			self::saveCallMaximizer($coachCallId, $response);

			DB::commit();

			return $data['newCallMax'];
		} catch (\Throwable$th) {
			DB::rollback();
            return false;
		}

		return null;
	}

    /**
     * function to add a new coachCall
     */
	public static function addNewCoachCall($data) {
		$response = CoachCall::addCoachCall($data);
		if ($response->coachCallId) {
			return $response->coachCallId;
		} else {
			return false;
		}
	}

    /**
     * This function will return the callmaximizer data
     * according to the callmaximizer date
     *
     * @param $callDate
     */
	public static function callCallMaxData($callDate) {

        $previousCallMaximizerDate = CoachCall::where('clientUserId', Auth::user()->user_id)->orderBy('coachCallId', 'DESC')->first();

		$selectDate = ($previousCallMaximizerDate['callDate']) ? $previousCallMaximizerDate['callDate'] : $callDate;

		$selectDate = Carbon::parse($selectDate)->format(Config::get('constants.dbDateFormat'));

		return CoachCall::
			where('clientUserId', Auth::user()->user_id)->where('callDate', $selectDate)->with('callMaximizerData')
			->get();

	}

	/**
	 * This function will save the callmaximizer data
	 * to the database
	 * @param $coachCallId, $coachCall
	 * @return bool
	 *
	 */
	public static function saveCallMaximizer($coachCallId, $coachCall) {
		$array = [];

		$coachCall = CallMaximizer::get();

		if ($coachCallId) {

			foreach ($coachCall as $key => $value) {

				if ($value->callMaximizerId <= Config::get('statistics.callMaxAssignEnd')) {

					$dataArray = array();

					$dataArray['coachCallId'] = $coachCallId;
					$dataArray['callMaximizerId'] = $value->callMaximizerId;

					if ($value->callMaximizerId <= Config::get('constants.callMaxCopy')) {
						$dataArray['updated'] = 0;
						$dataArray['satisfactionLevel'] = $value->satisfactionLevel;
						$dataArray['motivationLevel'] = $value->motivationLevel;
						$dataArray['longComment'] = '';
					} else {
						$dataArray['updated'] = 0;
						$dataArray['satisfactionLevel'] = 0;
						$dataArray['motivationLevel'] = 0;
						$dataArray['longComment'] = '';
					}

					array_push($array, $dataArray);
				}
			}
		} else {
			foreach ($coachCall as $key => $value) {
				if ($value->callMaximizerId <= Config::get('statistics.callMaxAssignEnd')) {

					$dataArray = [];

					$dataArray['coachCallId'] = $coachCallId;
					$dataArray['callMaximizerId'] = $value->callMaximizerId;
					$dataArray['updated'] = 0;
					$dataArray['satisfactionLevel'] = 0;
					$dataArray['motivationLevel'] = 0;
					$dataArray['longComment'] = '';

					array_push($array, $dataArray);
				}

			}
		}

		if ($array) {
			return CallMaximizerData::insert($array);
		} else {
			return false;
		}
	}

	/**
	 * This function will delete the callmaximizer date
	 * @param $id
	 */
	public function deleteCoachCallData($id) {
		$coachId = CoachCall::find($id)->delete();

		$findId = CallMaximizerData::where('coachCallId', $id)->delete();

		return $findId;
	}

	/**
	 * This function will update the call maximizer
	 * @param $id, $date
	 * @return bool
	 */
	public function updateCallMaximizer($id, $date) {
		$date = Carbon::parse($date)->format('Y-m-d');

        $callMaximizer = CoachCall::findOrFail($id);
        $callMaximizer->callDate = $date;

        $callMaximizer->save();

        return $callMaximizer;

	}

	/**
	 * This function will get the call maximizer data
	 */
	public static function getCallMaximizerComponent($coachCallId, $maximizerId) {
		return CallMaximizerData::where('coachCallId', $coachCallId)
			->where('callMaximizerId', $maximizerId)
		;
	}

	/**
	 * This funciton will update the callmaximizer data
	 * @param $coachCallId, $params
	 * @return bool
	 */
	public function updateData($params, $coachCallId) {

		$callMaximizerUpdate = [];
		foreach ($params as $key => $data) {

			if (self::getCallMaximizerComponent($coachCallId, $data['callMaximizerId'])->exists()) {
				$maximizerData = self::getCallMaximizerComponent($coachCallId, $data['callMaximizerId'])->get();


				foreach ($maximizerData as $key => $max) {
					$callMaximizerUpdate = CallMaximizerData::updateCallMaximizer($coachCallId, $data['callMaximizerId'], $data['longComment'], $data['satisfactionLevel'], $data['motivationLevel'], $data['updated']);
				}
			} else {
				$callMaximizerUpdate = CallMaximizerData::createCallMax($data['longComment'], $data['callMaximizerId'], $coachCallId);
			}
		}

		return $callMaximizerUpdate;

	}

    /**
     * delete the callmaximizer assignment component
     *
     * @param [type] $data
     * @return void
     */
    public function deleteCallMaximizerAssignmentComponent($data)
    {
        $deleteData = CallMaximizerData::where('callMaximizerDataId', $data)->delete();

        if($deleteData) {
            return true;
        } else {
            return false;
        }
    }

}
