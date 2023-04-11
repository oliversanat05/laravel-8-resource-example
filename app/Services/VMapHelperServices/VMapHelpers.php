<?php

namespace App\Services\VMapHelperServices;
use Config;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Succession\VMap;

class VMapHelpers{

    /***
     * Create a function to get the vmap value drop down contents
     * In this function uses general class function
     *
     * @param array
     * @return json
     */
    public function delegatedUsers($data){

        $dataArray      = array();
        if(count($data)){
            foreach($data as $key=> $value){
                $mixArray   = array();
                $mixArray['value'] = isset($value->user->user_id)?$value->user->user_id:$key;
                $mixArray['label'] = isset($value->user->name)?$value->user->name:$value;
                $mixArray['role'] = isset($value->user->role_id)?$value->user->role_id:0;

                array_push($dataArray, $mixArray);
            }
        }
        return $dataArray;
    }

    /**
     * This function will remove the http and https from the given URL
     * @param link
     * @return link
     */
    public static function filterLink($link){
        return $link;
    }

    /**
     * This function will create an array of the data to update the DB for all levels
     * @param NA
     * @return object
     */
    public static function vMapLevelActivityUpdate($level, $haystack)
    {
        $level->assignDate = Carbon::parse($haystack['assignDate'])->format(Config::get('constants.dbDateFormat'));
        $level->dueDate = Carbon::parse($haystack['dueDate'])->format(Config::get('constants.dbDateFormat'));
        $level->daily = $haystack['daily'] ? $haystack['daily'] : 0;
        $level->weekly = $haystack['weekly'] ? $haystack['weekly'] : 0;
        $level->monthly =  $haystack['monthly'] ?? 0;
        $level->quarterly = $haystack['quarterly'] ?? 0;
        $level->annually = $haystack['annually'] ?? 0;
        $level->goal = $haystack['goal'] ?? 0;
        $level->delegateTo = $haystack['delegateTo'];
        $level->qualifierTo = $haystack['qualifierTo'];
        $level->showOnDashboard = ($haystack['showOnDashboard']) ? $haystack['showOnDashboard'] : 0;
        $level->includeInReporting = ($haystack['includeInReporting']) ? $haystack['includeInReporting'] : 0;
        $level->includeInAvatar = ($haystack['includeInAvatar']) ? $haystack['includeInAvatar'] : 0;
        $level->includeInProfile = ($haystack['includeInProfile']) ? $haystack['includeInProfile'] : 0;
        $level->trackSign = $haystack['trackSign'];
        $level->successScale = ($haystack['successScale']) ? $haystack['successScale'] : 0;
        $level->description = $haystack['description'];
        $level->seasonalGoal = ($haystack['seasonalGoal']) ? $haystack['seasonalGoal'] : 0;
        $level->statusId = $haystack['statusId'];
        $level->completedDate = $haystack['completedDate'] ? $haystack['completedDate'] : Carbon::parse($haystack['assignDate'])->format(Config::get('constants.dbDateFormat'));
        $level->url = $haystack['url'] ?? '';

        return $level;
    }

    /**
	 * This function will get the data related to
	 * the vMap Id
	 * @param vMapId
	 * @return collection
	 */
	public function getVmapContent($vMapId) {
		$status = Config::get('statistics.vMapStatus');

		$vMap = VMap::where('vMapId', $vMapId)->where('isDelete', true)->with(['values' => function ($query) use ($status) {

			$query->where('isDelete', true)->with(['kpis' => function ($query) {

				$query->where('isDelete', false)->whereIn('statusId', Config::get('constants.status'))->with(['strategy' => function ($query) {

					$query->where('isDelete', false)->whereIn('statusId', Config::get('constants.status'))->with(['project' => function ($query) {

						$query->where('isDelete', false)->whereIn('statusId', Config::get('constants.status'))->with(['criticalActivity' => function ($query) {

							$query->where('isDelete', false)->whereIn('statusId', Config::get('constants.status'));

						}]);

					}]);

				}]);

			}]);

		}]); // vMap table

		return $vMap;
	}

    /**
     * format the vmap data statuses
     *
     * @return void
     */
    public function formatStatus($id)
    {
        return ($id == 0) ? 'Pending' : (($id == 1) ? 'In Progress' : (($id == 2) ? 'Completed' : ''));
    }

    /**
     * get the delegate names for the vmap levels
     *
     * @param $ids
     * @return void
     */
    public static function processDelegatesIds($ids)
    {
        $delegateArray = [];
        if(isset($ids)) {
            $delegateArray = explode(',', $ids);

            // dd($delegateArray);
            if(!is_null($delegateArray)) {
                $users = User::whereIn('user_id', $delegateArray)->first()->toArray();

                $delegateData[] = $users['name'] ?? '';
            }
        }
        if($delegateData) {
            return implode(', ', $delegateData);
        }
    }
}
