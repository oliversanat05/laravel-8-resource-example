<?php

namespace App\Services\Dashboard;
use DB;
use Config;
use Request;
use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Succession\VMap;
use App\Models\Succession\Kpi;
use App\Models\Profile\Profile;
use App\Models\Succession\Value;
use App\Models\Succession\Project;
use App\Models\Succession\Strategy;
use App\Models\Profile\FilterProfile;
use App\Models\Tracking\DelegateUser;
use App\Models\Tracking\TrackingData;
use App\Models\Notification\AvatarAlert;
use App\Models\Succession\CriticalActivity;
use App\Models\AvatarDetails;
use Illuminate\Support\Arr;

class DashboardSystemService {

	/**
	 * this function will get the data from the database and pass it to the controller
	 * @param NA
	 * @return object
	 */
	public function userNotificationData($url, $type) {
		// $params = $url ? explode('/', $url) : $url;
		$response = $this->getNotificationData($url, $type);
		$ids = $response['dataArray'];
		$data = $this->manageNotificationData($ids);
		$settings = $response['settings'];

		return compact('data', 'settings', 'ids');
	}

	/** this function manage the DB query
	 * @param Data
	 * @return object
	 */
	function getNotificationData($param, $type) {

		$dataArray = array();

		// $type = isset($params[5]) ? $params[5] : null;
		// $encodeValue = isset($params[6]) ? $params[6] : 0;

		$data = AvatarAlert::where($type, $param)->pluck('activityType', 'ID');

		if ($data) {
			$data = $data->toArray();

			foreach ($data as $key => $value) {
				$dataArray[$value][] = $key;
			}
		}
		$settings = AvatarAlert::where($type, $param)->first();
		return compact('dataArray', 'settings');
	}

	/** this function manage the notification after get the data from DB
	 * @param Data
	 * @return array
	 */
	public function manageNotificationData($data) {

		$kpi = $strategy = $project = $criticalActivity = [];

		if ($data) {
			foreach ($data as $key => $value) {
				switch ($key) {
				case 'kpi':
					$kpi = Kpi::whereIn('kpiId', $value)->get();
					break;
				case 'strategy':
					$strategy = Strategy::whereIn('strategyId', $value)->get();
					break;
				case 'project':
					$project = Project::whereIn('projectId', $value)->get();
					break;
				case 'criticalActivity':
					$criticalActivity = CriticalActivity::whereIn('criticalActivityId', $value)->get();
					break;
				}
			}
		}
		return compact('kpi', 'strategy', 'project', 'criticalActivity');
	}

	/**
	 * This function will get the the date from database and arrange it to json for email notification.
	 * @param params
	 * @return array
	 */
	public function getArrangedData($caHealth) {
		$dataArray = [];
		$dialsArray = [];
		$activityArray = [];
		$types = Config::get('statistics.defaultLevel');
		$types = array_flip($types);
		$settings = $caHealth['settings'];
		$tracking = $caHealth['ids'];
		$caHealth = $caHealth['data'];
		$userId = $settings->userId;

		$profile = Profile::where('userId', $userId)->first();
		$profile = $profile ? $profile->toArray() : $profile;

		if ($caHealth) {
			foreach ($caHealth as $key => $data) {
				foreach ($data as $val => $value) {
					$dueFor = Carbon::parse($value['endDate'])->format('dmY');
					$avatar = [];
					$avatar = (object) [
						'id' => $value[$key . 'Id'],
						'type' => ($types[$key]) ? $types[$key] : $key,
						'title' => $value[$key . 'Name'],
						'dueDate' => Carbon::parse($value['dueDate'])->format(Config::get('constants.dataDisplayFormat')),
						'inAvatar' => boolval($value['includeInAvatar']),
						'inDashboard' => boolval($value['showOnDashboard']),
						'inTracking' => boolval($value['includeInReporting']),
						'inProfile' => boolval($value['includeInProfile']),
						'delegates' => $value['delegateTo'],
						'status' => $value['statusId'],
						'startDate' => Carbon::parse($value['assignDate'])->format(Config::get('constants.dateFormat')),
						'endDate' => Carbon::parse($value->dueDate)->format(Config::get('constants.dateFormat')),
						'goal' => ($value['goal']) ? $value['goal'] : $value['Goal'], // one table it has small goal and other table has Goal
						'actual' => 0,
						'runRate' => 0,
						'sign' => $value['trackSign'],
						'seasonalGoal' => $value['seasonalGoal'],
						'isAccumulate' => $value->isAccumulate,
					];

					$dueFor = Carbon::parse($value->dueDate)->format('Ymd');

					if (!isset($activityArray[$dueFor])) {
						$activityArray[$dueFor] = [];
					}
					array_push($activityArray[$dueFor], $avatar);
					array_push($dialsArray, $avatar);
					if (!isset($tracking[$value[$key . 'Id']])) {
						$tracking[$value[$key . 'Id']] = [];
					}

					$assignDate = Carbon::parse($avatar->startDate);
					$dueDate = Carbon::parse($avatar->endDate);
					$startDate = Carbon::parse($profile['rangeStartDate']);
					$endDate = Carbon::parse($profile['rangeEndDate']);

					$tracking[$value[$key . 'Id']]['start'] = ($startDate->between($assignDate, $dueDate)) ? $startDate->format(config('constants.dateFormat')) : (($endDate->between($assignDate, $dueDate)) ? $avatar->startDate : (($assignDate->between($startDate, $endDate)) ? $assignDate : false));
					$tracking[$value[$key . 'Id']]['end'] = ($endDate->between($assignDate, $dueDate)) ? $endDate->format(Config::get('constants.dateFormat')) : (($startDate->between($assignDate, $dueDate)) ? $avatar->endDate : (($dueDate->between($startDate, $endDate)) ? $dueDate : false));
					$tracking[$value[$key . 'Id']]['isAccumulate'] = $value->isAccumulate;
				}
			}
		}

		$filters = array();
		$filters['startDate'] = $profile['rangeStartDate'];
		$filters['endDate'] = $profile['rangeEndDate'];

		$tracking = $this->getKeyChanges($tracking);

		$trackingData = $this->getTrackingItems($tracking, $filters);

		ksort($activityArray);
		$activityArray = array_values($activityArray);

		$dataArray = $activityArray;
		return compact('dataArray', 'settings', 'trackingData', 'profile', 'dialsArray');
	}

	public function getKeyChanges($tracking) {
		$dataArray = array();

		if ($tracking) {

			foreach ($tracking as $key => $value) {
				if ($key == config('statistics.trackingLevelKpi.id')) {
					$dataArray['level2'] = $value;
				} else if ($key == config('statistics.trackingLevelStr.id')) {
					$dataArray['level3'] = $value;
				} else if ($key == config('statistics.trackingLevelPro.id')) {
					$dataArray['level4'] = $value;
				} else if ($key == config('statistics.trackingLevelCa.id')) {
					$dataArray['level5'] = $value;
				} else {
					$dataArray[$key] = $value;
				}
			}

		}
		return $dataArray;
	}

	/**
	 * Create a function to get the tracking data group to show it to dashboard actual calculation
	 * @param request parameter
	 * @return json
	 */
	public function getTrackingItems($trackingDataIds, $filters = array()) {

		$startDate = Request::get('beginingDate');
		$endDate = Request::get('endingDate');

		$response = [];

		if (isset($trackingDataIds['level2']) || isset($trackingDataIds['level3']) || isset($trackingDataIds['level4']) || isset($trackingDataIds['level5'])):

			$response = TrackingData::select(DB::raw('SUM(trackingValue) as total'), 'trackingDate', 'kpiId', 'strategyId', 'projectId', 'criticalActivityId', DB::raw('DATE_FORMAT(MAX(endDate), "%m/%d/%Y") as endDate'), 'trackingValue as lastValue', DB::raw('GROUP_CONCAT(trackingValue ORDER BY endDate ASC) AS trackingValue'),
				DB::raw('GROUP_CONCAT(endDate ORDER BY endDate ASC) AS dates'))
				->orWhere(function ($query) use ($trackingDataIds) {
					if (isset($trackingDataIds['level2']) && count($trackingDataIds['level2'])) {
						$query->orWhereIn('kpiId', $trackingDataIds['level2']);
					}

					if (isset($trackingDataIds['level3']) && count($trackingDataIds['level3'])) {
						$query->orWhereIn('strategyId', $trackingDataIds['level3']);
					}

					if (isset($trackingDataIds['level4']) && count($trackingDataIds['level4'])) {
						$query->orWhereIn('projectId', $trackingDataIds['level4']);
					}

					if (isset(
					$trackingDataIds['level5']) && count($trackingDataIds['level5'])) {
						$query->orWhereIn('criticalActivityId', $trackingDataIds['level5']);
					}

				})
				->where(function ($query) use ($startDate, $endDate) {
					if ($startDate != 0 && $endDate != 0) {
						return $query->where('startDate', '>=', Carbon::parse($startDate)->format(config('constants.dbDateFormat')))
							->where('endDate', '<=', Carbon::parse($endDate)->format(config('constants.dbDateFormat')));
					}

					return true;
				})
				->groupBy('kpiId', 'strategyId', 'projectId', 'criticalActivityId')
				->orderBy('endDate', 'DESC')
				->get();
			$response = ($response) ? $response->toArray() : $response;
		endif;
		$trackingData = $this->manageTrackingDataRecords($response, $trackingDataIds);
		return $trackingData;
	}

	/**
	 * this function will get the specific level tracking data and return
	 * @param request
	 * @return array
	 */
	function manageTrackingDataRecords($response, $trackingDataIds) {

		$dataArray = $response;

		if ($response) {

			foreach ($response as $key => $value) {

				if ($value['dates']) {

					$splitDates = explode(',', $value['dates']);
					$splitData = explode(',', $value['trackingValue']);

					$trackingValue = 0;
					$dataArray[$key]['total'] = 0;
					$levelType = $this->readLevelTypes($value);

					if (isset($trackingDataIds[$levelType]) && $trackingDataIds[$levelType]['start'] && $trackingDataIds[$levelType]['end']) {
						try {
							$startDate = Carbon::parse($trackingDataIds[$levelType]['start']);
							$endDate = Carbon::parse($trackingDataIds[$levelType]['end']);
						} catch (Exception $e) {

						}
						$isAccumulate = $trackingDataIds[$levelType]['isAccumulate'];
						foreach ($splitDates as $niddle => $haystack) {

							if (strtotime(date(Config::get('constants.dbDateFormat'), strtotime($haystack))) <= strtotime(date(Config::get('constants.dbDateFormat'), strtotime(Config::get('constants.minDate'))))) {
								$trackingDate = Carbon::now();
							} else {
								$trackingDate = Carbon::parse($haystack);
							}

							if ($trackingDate->timestamp >= $startDate->timestamp && $trackingDate->timestamp <= $endDate->timestamp) {
								if ($isAccumulate) {
									$trackingValue = isset($splitData[$niddle]) ? $splitData[$niddle] : 0;
								} else {
									$trackingValue += isset($splitData[$niddle]) ? $splitData[$niddle] : 0;
								}

							}

						}
						$dataArray[$key]['total'] = round($trackingValue, 2);

					}

				}

			}

			return $dataArray;
		}
	}

	/**
	 * this function will get the specific level tracking data and return
	 * @param request
	 * @return array
	 */

	public function readLevelTypes($value) {

		return ($value['kpiId']) ? $value['kpiId'] : (($value['strategyId']) ? $value['strategyId'] : (($value['projectId']) ? $value['projectId'] : (($value['criticalActivityId']) ? $value['criticalActivityId'] : false)));
	}

    public function getVmapByFilterId($id){
        $filterProfile = FilterProfile::whereFilterId($id)->orWhere('filter_parent_id', $id)->with(['vmaps:vMapId,userId,formTitle'])->get()->toArray();

        $data = [];
        $dashboardProfileData = [];
                foreach($filterProfile as $key => $dashboardProfile) {


                    $dashboardProfileData['values'] = explode(',', $dashboardProfile['value_id']);
                    $dashboardProfileData['kpis'] = explode(',', $dashboardProfile['kpi_id']);
                    $dashboardProfileData['strategies'] = explode(',', $dashboardProfile['strategy_id']);
                    $dashboardProfileData['projects'] = explode(',', $dashboardProfile['project_id']);
                    $dashboardProfileData['criticalActivities'] = explode(',', $dashboardProfile['critical_activity_id']);
                    $dashboardProfileData['delegates'] = explode(',', $dashboardProfile['delegate_id']);



                    $filterProfile[$key]['values'] = $this->getVmapValues($dashboardProfileData['values']);
                    $filterProfile[$key]['kpis'] = $this->getVmapKpis($dashboardProfileData['kpis']);
                    $filterProfile[$key]['strategies'] = $this->getVmapStrategy($dashboardProfileData['strategies']);
                    $filterProfile[$key]['projects'] = $this->getVmapProjects($dashboardProfileData['projects']);
                    $filterProfile[$key]['criticalActivities'] = $this->getVmapCriticalActivities($dashboardProfileData['criticalActivities']);
                    $filterProfile[$key]['delegates'] = $this->getVmapDelegates($dashboardProfileData['delegates']);
                }

        return $filterProfile;



    }

    /**
     * get the vmap level 1 related to the particular dashboard profile
     *
     * @param array $valueId
     * @return array
     */
    public function getVmapValues(array $valueId)
    {
        $values = Value::whereIn('valueId', $valueId)->orderBy('displayOrder', 'ASC')->get(['valueId', 'vMapId', 'valueTitle', 'displayOrder'])->toArray();

        return $values;
    }

    /**
     * get the vmap level 2 related to the particular dashboard profile
     *
     * @param array $kpiId
     * @return array
     */
    public function getVmapKpis(array $kpiId)
    {
        $kpis = Kpi::whereIn('kpiId', $kpiId)->get(['kpiId', 'valueId', 'kpiName'])->toArray();
        return $kpis;
    }

    /**
     * get the vmap level 3 related to the particular dashboard profile
     *
     * @param array $strategyId
     * @return array
     */
    public function getVmapStrategy(array $strategyId)
    {
        $strategies = Strategy::whereIn('strategyId', $strategyId)->get(['strategyId', 'kpiId', 'strategyName'])->toArray();
        return $strategies;
    }

    /**
     * get the vmap level 4 related to the particular dashboard profile
     *
     * @param array $projectId
     * @return array
     */
    public function getVmapProjects(array $projectId)
    {
        $projects = Project::whereIn('projectId', $projectId)->get(['projectId', 'strategyId', 'projectName'])->toArray();

        return $projects;
    }

    /**
     * get the vmap level 5 according to the particular dashboard profile
     *
     * @param array $criticalActivityId
     * @return array
     */
    public function getVmapCriticalActivities(array $criticalActivityId)
    {
        $criticalActivity = CriticalActivity::whereIn('criticalActivityId', $criticalActivityId)->get(['criticalActivityId', 'projectId', 'criticalActivityName'])->toArray();
        return $criticalActivity;
    }

    /**
     * get all the delegates regarding the dashboard profile
     *
     * @param array $delegateId
     * @return array
     */
    public function getVmapDelegates(array $delegateId)
    {

        $delegateList = DelegateUser::whereIn('delegateUsersId', $delegateId)
            ->with(['user:user_id,name,user_name'])
            ->whereNotNull('userId')
            ->get(['delegateUsersId', 'userId'])->toArray();

        return $delegateList;
    }
	/**
     * This function will get the the date from database and arrange it to json.
     * @param NA
     * @return object
     */
    public function calUserDataSummery($filters)
    {
		$user = Auth::user();

		$userDelegates = DelegateUser::where('parentId', $user->user_id)->with(['user' => function($query) {
			return $query->select(['user_id', 'name']);
		}])->get()->pluck('user.name', 'user.user_id')->toArray();

		$data = $this->getUserDelegateData($filters);

		return self::serializeSummaryData($data, $filters, $userDelegates);
	}

	public function serializeSummaryData($filterData,$filters, $userDelegates)
	{
		$dataArray = array();
		$activityArray = array();
		$performanceArray = array();
		$trackingDataIds = array();

		if( count($filterData ) )
		{
			foreach($filterData as $key1 => $caHealth)
			{

				if($caHealth)
				{

					foreach($caHealth as $key2 => $value){

						$delegates		= ($value->delegateTo) ? explode(',', $value->delegateTo) : [Auth::user()->user_id];
						$start			= ($filters['startDate'] != 0)?Carbon::parse($filters['startDate'])->timestamp:Carbon::now()->timestamp;
						$end			= ($filters['startDate'] != 0)?Carbon::parse($filters['endDate'])->timestamp:Carbon::now()->timestamp;
						$target			= ($value->dueDate != 0)?Carbon::parse($value->dueDate)->timestamp:Carbon::now()->timestamp;

						foreach($delegates as $key3 => $delegate){
							if($delegate && isset($userDelegates[$delegate]) && (boolval($value->inAvatar) || boolval($value->inDashboard) || boolval($value->inProfile)))
							{
								if (! isset($dataArray[$delegate])){

                                    // dd($userDelegates[$delegate]);

									$dataArray[$delegate] = (object) [
									  'id' => $delegate,
									  'name' => $userDelegates[$delegate],
									  'avatarActivities' => [],
									  'avatarPerformance' => [],
									  'performanceProfile' => [],
									  'activityProfile' => []
									];
								}

								$avatar = (object) [
									'id' => $value->Id,
									'type' => $key1,
									'title' => parseString($value->name),
									'dueDate' => Carbon::parse($value->dueDate)->format(config('constants.dateFormat')),
									'inAvatar' => boolval($value->inAvatar),
									'inDashboard' => boolval($value->inDashboard),
									'inTracking' => boolval($value->inTracking),
									'inProfile' => boolval($value->inProfile),
									'delegates' => $value->delegates,
									'status' => $value->status,
									'level1Name' => ($value->level1Name)?$value->level1Name:null,
									'level2Name' => ($value->level2Name)?$value->level2Name:null,
									'level3Name' => ($value->level3Name)?$value->level3Name:null,
									'level4Name' => ($value->level4Name)?$value->level4Name:null,
									'startDate' => Carbon::parse($value->assignDate)->format(config('constants.dateFormat')),
									'endDate' => Carbon::parse($value->dueDate)->format(config('constants.dateFormat')),
									'goal' => $value->goal,
									'actual' => 0,
									'runRate' => 0,
									'sign'=>$value->sign,
									'seasonalGoal' =>$value->seasonalGoal,
									'isAccumulate' => $value->isAccumulate,
									'vMapId' => $value->vMapId,
								];

								/* this activity will display in activity section*/
								if ($value->inAvatar && $target >= $start && $target <= $end)
									array_push($dataArray[$delegate]->avatarActivities, $avatar);

								/* this activity will display in Performance dial section*/
								if ($value->inDashboard && $target >= $start && $target <= $end)
									array_push($dataArray[$delegate]->avatarPerformance, $avatar);

								/* this activity will display in activity avatar section*/
								if ($value->inProfile && $value->inAvatar && $target >= $start && $target <= $end)
									array_push($dataArray[$delegate]->activityProfile, $avatar);

								/* this activity will display in Performance avatar section*/
								if ($value->inProfile && $value->inDashboard && $target >= $start && $target <= $end)
									array_push($dataArray[$delegate]->performanceProfile, $avatar);


								/* tracking data for activity to calculate the run rate*/
								if ($value->inProfile || $value->inDashboard){
									if(!isset($trackingDataIds[$key1]))
										$trackingDataIds[$key1] = [];

									array_push($trackingDataIds[$key1], $value->Id);

									if(!isset($trackingDataIds[$value->Id]))
										$trackingDataIds[$value->Id] = [];

									$assignDate = Carbon::parse($avatar->startDate);
									$dueDate = Carbon::parse($avatar->endDate);
									$startDate =  Carbon::parse($filters['startDate']);
									$endDate =  Carbon::parse($filters['endDate']);

									$trackingDataIds[$value->Id]['start'] = ($startDate->between($assignDate, $dueDate))	?
																															$filters['startDate']:
																															(
																																(
																																	$endDate->between($assignDate, $dueDate))?$avatar->startDate:(($assignDate->between($startDate, $endDate))?$assignDate:false
																																)
																															);
									$trackingDataIds[$value->Id]['end'] = ($endDate->between($assignDate, $dueDate))?$filters['endDate']:(($startDate->between($assignDate, $dueDate))?$avatar->endDate:(($dueDate->between($startDate, $endDate))?$dueDate:false));
									$trackingDataIds[$value->Id]['isAccumulate'] = $value->isAccumulate;
								}
							}
						}

						if (isset($avatar) && $value->inAvatar)
						{
							$dueFor = Carbon::parse($value->dueDate)->format('Y-m-d');

							if($target >= $start && $target <= $end)
							{

								if (! isset($activityArray[$dueFor])){
									$activityArray[$dueFor]['data'] = [];
								}

							  	array_push($activityArray[$dueFor]['data'], $avatar);
							}
						}
					}
				}
			}
		}

		$trackingData	= self::getTrackingItems($trackingDataIds, $filters);
		$dataArray		= array_values($dataArray);

		krsort($activityArray);

		$activityArraykeys  = array_keys($activityArray);
		$activityArray		= array_values($activityArray);

		$status = self::getDashboardStatusList();
		foreach ($dataArray as $key => $value)
		{
			$avatarDetails = AvatarDetails::where('userId', $value->id)->get();
			$dataArray[$key]->health = $avatarDetails;
		}

		return  compact('activityArray', 'dataArray', 'trackingData', 'status', 'activityArraykeys');
	}

	 /**
	 * This function will get the list of the status which is used as global
	 * @param NA
	 * @return object
	 */
	public function getDashboardStatusList(){
		/* this array will create a dropdown for Show all status */
		$dataArray = array();
		array_push($dataArray,Config::get('statistics.statusPending'));
		array_push($dataArray,Config::get('statistics.statusInProgress'));
		array_push($dataArray,Config::get('statistics.statusPendingProgress'));
		array_push($dataArray,Config::get('statistics.statusCompleted'));
		array_push($dataArray,Config::get('statistics.statusAll'));
		return $dataArray;
	}

	/**
     * This function will get vMap data for dashboard delegate activity section
     * @param NA
     * @return object
     */
    public function getUserDelegateData($filters)
    {

        $level5     = $this->calUserHealthCa($filters);
        $level2     = $this->calUserHealthKpi($filters);
        $level3     = $this->calUserHealthStrategy($filters);
        $level4     = $this->calUserHealthProject($filters);

        return compact('level5', 'level2', 'level3', 'level4');
    }

	/**
     * This function will get vMap data for dashboard delegate section
     * @param NA
     * @return object
     */
    public function calUserHealthCa($filters)
    {
      $delegate = self::getDelegateCause($filters,'c');
        $array = ['vMap.vMapId AS vMapId','vMap.formTitle AS vMapName','v.valueId AS level1','k.kpiId AS level2','s.strategyId AS level3','p.projectId AS level4','c.criticalActivityId as Id','c.statusId as status','c.delegateTo as delegateTo','criticalActivityName AS name', 'c.assignDate', 'c.dueDate','criticalActivityName AS title', 'c.includeInAvatar AS inAvatar', 'c.showOnDashboard as inDashboard',
          'c.includeInReporting AS inTracking', 'c.delegateTo AS delegates','v.valueTitle AS level1Name','k.kpiName AS level2Name','s.strategyName as level3Name','p.projectName as level4Name','c.goal as goal','c.trackSign as sign','c.seasonalGoal as seasonalGoal','c.isAccumulate AS isAccumulate','c.includeInProfile AS inProfile'];

          return VMap::select($array)
            ->leftjoin('value AS v','v.vMapId', '=', 'vMap.vMapId')
            ->leftjoin('kpi AS k','k.valueId', '=', 'v.valueId')
            ->leftjoin('strategy AS s','s.kpiId', '=', 'k.kpiId')
            ->leftjoin('project AS p','p.strategyId', '=', 's.strategyId')
            ->leftjoin('criticalActivity AS c','p.projectId', '=', 'c.projectId')
            ->where('userId', Auth::user()->user_id)
            ->where(function($query) use($filters) {
              if($filters['activeStatus'] != ''):
                if($filters['activeStatus'] == config('statistics.statusPendingProgress.id')){
                  $query->whereIn('c.statusId',config('statistics.statusBoth'));
                }
                else
                  $query->where('c.statusId',$filters['activeStatus']);
              else:
                $query->whereIn('c.statusId',config('statistics.vMapStatus'));
              endif;
            })
            ->where(function($query) use($filters) {
              if($filters['activeVMap'])
                $query->whereIn('vMap.vMapId',$filters['activeVMap']);
              if($filters['activeValue'])
                $query->whereIn('v.valueId',$filters['activeValue']);
              if($filters['activeKpi'])
                $query->whereIn('k.kpiId',$filters['activeKpi']);
              if($filters['activeCriticalActivity']  && $filters['activeProfile'] > 0)
                $query->whereIn('c.criticalActivityId',$filters['activeCriticalActivity']);

            })
            ->where(function($query) use($delegate){
              if($delegate){
                foreach($delegate as $select) {
                   $query->orWhereRaw($select);
                }
              }
            })
            ->where('vMap.isDelete', true)->where('v.isDelete', true)->where('c.isDelete', false)
            ->where('p.isDelete', false)->where('s.isDelete', false)->where('k.isDelete', false)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * This function will get the level activity data once data saved in DB with their all parent.
     * @param NA
     * @return object
     */
    private function calUserHealthKpi($filters)
    {
        $delegate = self::getDelegateCause($filters,'k');
        $array = ['vMap.vMapId AS vMapId','vMap.formTitle AS vMapName','v.valueId AS level1','k.kpiId as Id','k.statusId as status','k.delegateTo as delegateTo','kpiName AS name','k.goal as goal','assignDate', 'dueDate','kpiName AS title', 'k.includeInAvatar AS inAvatar', 'k.showOnDashboard as inDashboard', 'k.includeInReporting AS inTracking', 'k.delegateTo AS delegates','v.valueTitle AS level1Name','k.trackSign as sign','k.seasonalGoal as seasonalGoal','k.isAccumulate AS isAccumulate','k.includeInProfile AS inProfile'];
        return VMap::select($array)
          ->leftjoin('value AS v','v.vMapId', '=', 'vMap.vMapId')
          ->leftjoin('kpi AS k','k.valueId', '=', 'v.valueId')
          ->where('userId', Auth::user()->user_id)
          ->where(function($query) use($filters) {
            if($filters['activeVMap'])
                $query->whereIn('vMap.vMapId',$filters['activeVMap']);
              if($filters['activeValue'])
                $query->whereIn('v.valueId',$filters['activeValue']);
              if($filters['activeKpi'])
                $query->whereIn('k.kpiId',$filters['activeKpi']);
          })
          ->where(function($query) use($delegate){
            if($delegate){
              foreach($delegate as $select) {
                 $query->orWhereRaw($select);
              }
            }
          })
          ->where(function($query) use($filters) {
            if($filters['activeStatus'] != ''):
              if($filters['activeStatus'] == config('statistics.statusPendingProgress.id')){
                $query->whereIn('k.statusId',config('statistics.statusBoth'));
              }
              else
                $query->where('k.statusId',$filters['activeStatus']);
            else:
                $query->whereIn('k.statusId',config('statistics.vMapStatus'));
            endif;
          })
          ->where('vMap.isDelete', true)->where('v.isDelete', true)->where('k.isDelete', false)
          ->orderBy('name', 'ASC')
          ->get();
    }

    /**
     * This function will get the level activity data once data saved in DB with their all parent.
     * @param NA
     * @return object
     */
    private function calUserHealthStrategy($filters)
    {
      $delegate = self::getDelegateCause($filters,'s');
        $array = ['vMap.vMapId AS vMapId','vMap.formTitle AS vMapName','v.valueId AS level1','k.kpiId AS level2','s.strategyId as Id','s.statusId as status','s.delegateTo as delegateTo','strategyName AS name','s.goal as goal','s.assignDate', 's.dueDate','strategyName AS title', 's.includeInAvatar AS inAvatar', 's.showOnDashboard as inDashboard', 's.includeInReporting AS inTracking', 's.delegateTo AS delegates',
          'v.valueTitle AS level1Name','k.kpiName AS level2Name','s.trackSign as sign','s.seasonalGoal as seasonalGoal','s.isAccumulate AS isAccumulate','s.includeInProfile AS inProfile'];

        return VMap::select($array)
          ->leftjoin('value AS v','v.vMapId', '=', 'vMap.vMapId')
          ->leftjoin('kpi AS k','k.valueId', '=', 'v.valueId')
          ->leftjoin('strategy AS s','s.kpiId', '=', 'k.kpiId')
          ->where('userId', Auth::user()->user_id)
          ->where(function($query) use($filters) {
            if($filters['activeVMap'])
                $query->whereIn('vMap.vMapId',$filters['activeVMap']);
              if($filters['activeValue'])
                $query->whereIn('v.valueId',$filters['activeValue']);
              if($filters['activeKpi'])
                $query->whereIn('k.kpiId',$filters['activeKpi']);
              if($filters['activeStrategy']  && $filters['activeProfile'] > 0)
                $query->whereIn('s.strategyId',$filters['activeStrategy']);
          })
          ->where(function($query) use($delegate){
            if($delegate){
              foreach($delegate as $select) {
                 $query->orWhereRaw($select);
              }
            }
          })
          ->where(function($query) use($filters) {
            if($filters['activeStatus'] != ''):
              if($filters['activeStatus'] == config('statistics.statusPendingProgress.id')){
                $query->whereIn('s.statusId',config('statistics.statusBoth'));
              }
              else
                $query->where('s.statusId',$filters['activeStatus']);
            else:
                $query->whereIn('s.statusId',config('statistics.vMapStatus'));
            endif;
          })
          ->where('vMap.isDelete', true)->where('v.isDelete', true)->where('s.isDelete', false)
          ->where('k.isDelete', false)->orderBy('name', 'ASC')->get();
    }

    /**
     * This function will get the level activity data once data saved in DB with their all parent.
     * @param NA
     * @return object
     */
    private function calUserHealthProject($filters)
    {
      $delegate = self::getDelegateCause($filters,'p');
        $array = ['vMap.vMapId AS vMapId','vMap.formTitle AS vMapName','v.valueId AS level1','k.kpiId AS level2','s.strategyId AS level3','p.projectId as Id','p.statusId as status','p.delegateTo as delegateTo','projectName AS name','p.goal as goal','p.assignDate', 'p.dueDate','projectName AS title', 'p.includeInAvatar AS inAvatar', 'p.showOnDashboard as inDashboard', 'p.includeInReporting AS inTracking', 'p.delegateTo AS delegates','v.valueTitle AS level1Name','k.kpiName AS level2Name','s.strategyName as level3Name','p.trackSign as sign','p.seasonalGoal as seasonalGoal','p.isAccumulate AS isAccumulate','p.includeInProfile AS inProfile'];

        return VMap::select($array)
            ->leftjoin('value AS v','v.vMapId', '=', 'vMap.vMapId')
            ->leftjoin('kpi AS k','k.valueId', '=', 'v.valueId')
            ->leftjoin('strategy AS s','s.kpiId', '=', 'k.kpiId')
            ->leftjoin('project AS p','p.strategyId', '=', 's.strategyId')
            ->where('userId', Auth::user()->user_id)
            ->where(function($query) use($filters) {
              if($filters['activeVMap'])
                $query->whereIn('vMap.vMapId',$filters['activeVMap']);
              if($filters['activeValue'])
                $query->whereIn('v.valueId',$filters['activeValue']);
              if($filters['activeKpi'])
                $query->whereIn('k.kpiId',$filters['activeKpi']);
              if($filters['activeStrategy']  && $filters['activeProfile'] > 0)
                $query->whereIn('s.strategyId',$filters['activeStrategy']);
              if($filters['activeProject']  && $filters['activeProfile'] > 0)
                $query->whereIn('p.projectId',$filters['activeProject']);
            })
            ->where(function($query) use($delegate){
              if($delegate){
                foreach($delegate as $select) {
                   $query->orWhereRaw($select);
                }
              }
            })
            ->where(function($query) use($filters) {
              if($filters['activeStatus'] != ''):
                if($filters['activeStatus'] == config('statistics.statusPendingProgress.id')){
                  $query->whereIn('p.statusId',config('statistics.statusBoth'));
                }
                else
                  $query->where('p.statusId',$filters['activeStatus']);
              else:
                $query->whereIn('p.statusId',config('statistics.vMapStatus'));
              endif;
            })
            ->where('vMap.isDelete', true)->where('v.isDelete', true)->where('p.isDelete', false)
            ->where('s.isDelete', false)->where('k.isDelete', false)
            ->orderBy('name', 'ASC')->get();
    }

	/**
     * This function will helpquery to built the find in set
     * @param filter array and type
     * @return object
     */
    public function getDelegateCause($filters,$type){
		$delegate = [];
		$delegates = is_array($filters['activeDelegate'])?$filters['activeDelegate']:array($filters['activeDelegate']);
		if(is_array($delegates) && $filters['activeDelegate']) {

            foreach ($delegates as $key => $value) {
                array_push($delegate, 'FIND_IN_SET('.$value.','.$type.'.delegateTo)');
            }
        }
		return $delegate;
    }

	/*
     * this function will get the specific level tracking data and return
     * @param request
     * @return array
     */
    public function callTrackingValues(){

		$trackingRecord = [];
        $dataArray      = array();
        $parents        = null;
        $type           = Request::get('level');

        $constant   = Config::get('statistics.defaultLevel');
        $level =    $constant[$type];

        $id = Request::get('id');

        $data =  TrackingData::where($level.'Id', $id)->get();

        switch($type){
          case 'level2':
              $parents = Kpi::with('value')->where('kpiId', $id);
          break;
          case 'level3':
              $parents = Strategy::with('kpi.value')->where('strategyId', $id);
          break;
          case 'level4':
              $parents = Project::with('strategy.kpi.value')->where('projectId', $id);
          break;
          case 'level5':
              $parents = CriticalActivity::with('project.strategy.kpi.value')->where('criticalActivityId', $id);
          break;
        }
        $response = $parents->first();
        $newData = ($response)?$response->toArray():null;
        $dataArray['data'] = self::getArrange($data, $id, $level);
        $dataArray['vmapLink'] = array_reverse($this->getParents($newData, $level));
        $dataArray['type']  = $level;
		$dataArray['id']  = $id;
		$dataArray['activityId']  = $id;
        $dataArray['status']= $newData['statusId'];

		array_push($trackingRecord, $dataArray);
        return ['data' => $trackingRecord];
    }
	/*
     * this function will arange the data after get the specific level tracking data and return
     * @param request
     * @return array
     */
    public function getArrange($data, $id, $level){

        $dataArray      = array();
        if($data):
            $data       = $data->toArray();

            foreach ($data As $key => $haystack):

                $newArray       = array();
                $newArray['trackingId'] = $haystack['trackingDataId'];
                $newArray['beginningDate'] = Carbon::parse($haystack['startDate'])->format(Config::get('constants.dateFormat'));
                $newArray['endingDate'] = Carbon::parse($haystack['endDate'])->format(Config::get('constants.dateFormat'));
                $newArray['data'] = $haystack['trackingValue'];
                $newArray['comment'] = $haystack['comment'];
				$newArray['activity'] = $level;
                $newArray['activityId'] = $id;
                array_push($dataArray, $newArray);
            endforeach;
        endif;
        return $dataArray;
    }

	/*
   * this function will serialize the parent of eact ectivity
   * $param array
   * @return array
   */

public function getParents($data, $level){
    $return = [];
    $titles = [
      'criticalActivity' => 'criticalActivityName',
      'project'          => 'projectName',
      'strategy'         => 'strategyName',
      'kpi'              => 'kpiName',
      'value'            => 'valueTitle'
    ];

    $levels = array_keys($titles);
    $thisLevel = $level;
    $thisTitle = $data[$titles[$level]];
    $levels = array_slice($levels, array_search($level, $levels) + 1);

    foreach ($titles as $level => $title) {
      $name = implode('.', $levels);
      if($name) {
        $title = $name . '.' . $titles[end($levels)];
        $type = array_pop($levels);
        array_push($return, ['type' => $type, 'title' => Arr::dot($data)[$title]]);
      }
    }

    array_push($return, ['type' => $thisLevel, 'title' => $thisTitle]);

    return array_reverse($return);
	}

    /**
     * for updating the data in the level's description from the dashboard
     *
     * @param [type] $data
     * @return void
     */
    public function quickUpdate($params)
    {
        foreach($params as $key => $param) {
            switch ($param['type']) {
                case 'level2':
                    $this->kpiQuickUpdate($param);
                    break;
                case 'level3':
                    $this->strategyQuickUpdate($param);
                    break;
                case 'level4':
                    $this->projectQuickUpdate($param);
                    break;
                case 'level5':
                    $this->criticalActivityQuickUpdate($param);
                    break;
                default:
                    break;
            }
        }

        return true;
    }

    /**
     * for updating the kpi
     *
     * @param [type] $activity
     * @return void
     */
    public function kpiQuickUpdate($activity)
    {

        $kpi = Kpi::find($activity['id']);

        if(isset($activity['dueDate'])) {
            $kpi->dueDate = $this->updateDueDate($activity['dueDate']);
        }

        if(isset($activity['description'])) {
            $kpi->description = $this->appendDescription($kpi->description, $activity['description']);
        }

        return $kpi->save();
    }

    /**
     * for updating the strategy quick update
     *
     * @param [type] $activity
     * @return void
     */
    public function strategyQuickUpdate($activity)
    {
        $strategy = Strategy::find($activity['id']);

        if(isset($activity['dueDate'])) {
            $strategy->dueDate = $this->updateDueDate($activity['dueDate']);
        }

        if(isset($activity['description'])) {
            $strategy->description = $this->appendDescription($strategy->description, $activity['description']);
        }

        return $strategy->save();
    }

    /**
     * for updating the project description and due date from the dashboard
     *
     * @param [type] $activity
     * @return void
     */
    public function projectQuickUpdate($activity)
    {
        $project = Project::find($activity['id']);

        if(isset($activity['dueDate'])) {
            $project->dueDate = $this->updateDueDate($activity['dueDate']);
        }

        if(isset($activity['description'])) {
            $project->description = $this->appendDescription($project->description, $activity['description']);
        }

        return $project->save();
    }

    /**
     * for updating the critical activity description and due date from the dashboard
     *
     * @param [type] $activity
     * @return void
     */
    public function criticalActivityQuickUpdate($activity)
    {
        $criticalActivity = CriticalActivity::find($activity['id']);

        if(isset($activity['dueDate'])) {
            $criticalActivity->dueDate = $this->updateDueDate($activity['dueDate']);
        }

        if(isset($activity['description'])) {
            $criticalActivity->description = $this->appendDescription($criticalActivity->description, $activity['description']);
        }

        return $criticalActivity->save();
    }

    /**
     * for updating the due date of the levels after parsing
     *
     * @param [type] $date
     * @return void
     */
    public function updateDueDate($date)
    {
        return Carbon::parse($date)->format(Config::get('constants.dbDateFormat'));
    }

    /**
     * for appending the description to the existing description for the levels
     *
     * @param [type] $description
     * @param [type] $newDescription
     * @return void
     */
    public function appendDescription($description, $newDescription)
    {
        $appendDescription = "";
        $decodeDescription = json_decode($description);

        if(isset($decodeDescription->blocks)) {
            array_unshift($decodeDescription->blocks, (object) [
                'key' => '',
                'text' => $newDescription,
                'type' => 'unstyled',
                'depth' => 0,
                'inlineStyleRanges' => [],
                'entityRanges' => [],
                'data' => (object) []
            ]);

            return json_encode($decodeDescription);
        } else {
            $data = explode('~#~', $description);
            if(isset($data[0]) && isset($data[1])) {
                $appendDescription = $newDescription.$data[0];
                $data[0] = $appendDescription;
                return implode('~#~', $data);
            }

            $appendDescription = $newDescription.$description;
            $description = $appendDescription;
            return $description;
        }
    }

    /**
     * to get the dashboard avatar global health
     */
    public function getDashboardUserAvatarHealth()
    {
        $profile = Auth::user()->profile;

        $filter = [
            'activeProfile' => 0,
            'activeVMap' => 0,
            'activeValue' => 0,
            'activeKpi' => 0,
            'activeStrategy' => 0,
            'activeProject' => 0,
            'activeCriticalActivity' => 0,
            'activeStatus' => config('statistics.vMapConstant.InProgress'),
            'activeDelegate' => 0,
            'startDate' => $profile->rangeStartDate,
            'endDate' => $profile->rangeEndDate
        ];

        $data = $this->calUserDataSummery($filter);

        $health = [
            'avatarActivities' => (object)[
                'count' => 0,
                'average' => 0,
                'grade' => null
            ],
            'avatarPerformance' => (object)[
                'count' => 0,
                'average' => 0,
                'grade' => null
            ]
        ];

        $startDate = ($filter['startDate']) ? Carbon::parse($filter['startDate']) : Carbon::parse('first day of January');
        $endDate = ($filter['startDate']) ? Carbon::parse($filter['endDate']) : Carbon::parse('last day of december');

        $avatarActivities = [];
        $avatarPerformance = [];

        $trackingData = $data['trackingData'];

        if (count($data['dataArray'])) {
            foreach ($data['dataArray'] as $key1 => $delegate) {
                if (count($delegate->activityProfile) > 0) {
                $delegateActivities = [];
                foreach ($delegate->activityProfile as $key2 => $avatarActivitiy) {

                    $dueDate = Carbon::parse($avatarActivitiy->dueDate);
                    //date range condition
                    if($dueDate->timestamp >= $startDate->timestamp && $dueDate->timestamp <= $endDate->timestamp) {
                        $health['avatarActivities']->count++;
                        array_push($delegateActivities, $this->getActivityHealth($avatarActivitiy->dueDate));
                    }
                }
                    if($delegateActivities)
                        $avatarActivities[$delegate->id] = $this->getAverage($delegateActivities);
                }

                    if (count($delegate->performanceProfile) > 0) {

                    $delegatePerformances = [];

                    foreach ($delegate->performanceProfile as $key3 => $performanceActivitiy) {

                        $health['avatarPerformance']->count++;
                        $runrate = $this->runRateCalculation($performanceActivitiy, $trackingData, $profile);
                        array_push($delegatePerformances, $runrate);
                    }
                    $avatarPerformance[$delegate->id] = $this->getAverage($delegatePerformances);
                }
            }
          }

            $avatarAverage = $this->getAverage($avatarActivities);
            $performanceAverage = $this->getAverage($avatarPerformance);

            $health['avatarActivities']->average = $avatarAverage < $profile->minPerformAv ? $profile->minPerformAv : $avatarAverage;
            $health['avatarActivities']->average = $avatarAverage > $profile->maxPerformAv ? $profile->maxPerformAv : $avatarAverage;

            $health['avatarPerformance']->average = $performanceAverage;
            return $health;
    }

    public function getAverage($dataArray) {
        $average = 0;
        try {
            if (count($dataArray)) {
                $average = round(array_sum($dataArray) / count($dataArray), 2);
            }
        } catch (Exception $e) {
            $average = 0;
        }
        return round($average);
      }

    public function getActivityHealth($date) {
        $date = Carbon::parse($date);
        $now  = Carbon::now();
        $dateDiff = $now->diffInDays($date, false);
        $health;

        if( $dateDiff >= 0 )
            $health = 100;
        elseif( $dateDiff < 0  && $dateDiff >= -9)
            $health = 80;
        elseif( $dateDiff <= -10  && $dateDiff >= -15)
            $health = 60;
        elseif( $dateDiff <= -16  && $dateDiff >= -20)
            $health = 40;
        elseif( $dateDiff <= -21  && $dateDiff >= -25)
            $health = 20;
        elseif($dateDiff <= -25)
            $health = 0;
        return $health;
    }

    /**
     * this function calculates the new run rate based on the Goal and Actual
    * @param activity data
    * @return run rate (float)
    */
    public function runRateCalculation($data, $trackingData, $userProfile){

        $dataArray  = array();
        $tracking   = 0;
        $goal       = 0;
        $goalvalue  = 0;
        $trackingArray = array();

        $types      = config('statistics.defaultLevel');

        $type   = $types[$data->type];
        $index  = array_search($data->id, array_column($trackingData, $type.'Id'));
        $index  = is_numeric($index)?$index:-1;
        if(count($trackingData) && $index >= 0 ):

          if($data->isAccumulate):
            try{
              $trackingArray  = $trackingData[$index];
              $trackingvalues = explode(',',$trackingData[$index]['total']);
              $tracking       = $trackingvalues[count($trackingvalues)-1];

            }catch(\Exception $e){
              $tracking = $trackingData[$index]['total'];
            }
          else:
            $trackingArray  = $trackingData[$index];
            $tracking = $trackingData[$index]['total'];
          endif;
        endif;

        $goal = $this->getGoal($data, $trackingArray, $userProfile);

        try{
          if($goal !=0 && $tracking != 0 && $index >= 0):
            $goalvalue = $tracking/$goal;
            $goalvalue = round($goalvalue*100);
          endif;

        }catch(Exception $e) {
          $goalvalue = 0;
        }

        // setting max and min value set for each activity
        if($goalvalue > $userProfile->maxPerformAv)
          $goalvalue = $userProfile->maxPerformAv;
        else if(intval($goalvalue) < $userProfile->minPerformAv)
          $goalvalue = $userProfile->minPerformAv;


        return $goalvalue;
      }

    /*
    * this function calculates the goal based on the date filters set
    * @param array, trackingArray
    * @return float (goal)
    */
      public function getGoal($data, $trackingArray, $userProfile){

        $goal = 0;
        $lastDate = 0;
        $startDate = $data->startDate;
        $endDate = $data->endDate;

        $activityDateDiff = Carbon::parse($data->startDate)->diffInMonths(Carbon::parse($data->endDate))+1;

        $filterStartDate  = $userProfile['rangeStartDate'];
        $filterEndDate  = $userProfile['rangeEndDate'];

        $startDate  = (Carbon::parse($startDate)->timestamp <= Carbon::parse($filterStartDate)->timestamp)?$filterStartDate:$startDate;
        $endDate  = (Carbon::parse($endDate)->timestamp <= Carbon::parse($filterEndDate)->timestamp)?$endDate:$filterEndDate;
        if($trackingArray):

          $lastDate = Carbon::parse($trackingArray['endDate']);
          $lastDate = ($lastDate->timestamp <= Carbon::parse($endDate)->timestamp)?$lastDate:Carbon::parse($endDate);
          try{
            $lastDateFormat = Carbon::parse($trackingArray['endDate'])->lastOfMonth()->format('d');
          }
          catch(\Excepton $e){
            $lastDateFormat = Carbon::now()->lastOfMonth()->format('d');
          }

          if($data->sign === config('statistics.seasonal')):

            $getGoalIndexes = self::getGoalIndexes($startDate, $endDate, $lastDate);
            $goalArray = explode(',',$data->seasonalGoal);

            if(count($goalArray)):

              foreach($goalArray AS $key => $value):

                if( in_array($key, $getGoalIndexes)):

                  // seasonal goal with do not accumulate active then calculate the goal based on it
                  ($data->isAccumulate)?$goal = $value:$goal += $value;
                endif;

              endforeach;

            endif;

          elseif($data):

            if($data->isAccumulate):
              $goal = $data->goal;

            else:
              $activityDateDiff = ($activityDateDiff)?$activityDateDiff:1;

              if($data->goal):

                $firstDate = Carbon::parse($startDate);
                $firstDateFormat = Carbon::parse($startDate)->lastOfMonth()->format('d');

                $firstDayOfYear = ($startDate)?Carbon::parse($startDate):Carbon::parse('first day of January');
                if($lastDate->format('d') == $lastDateFormat && $firstDate->format('d') == $firstDateFormat):
                  $dateDiff = $firstDayOfYear->diffInMonths($lastDate)+1;
                  $goal = ($data->goal/($activityDateDiff/$dateDiff));

                else:

                  try{
                    $activityDateDiff = Carbon::parse($data->startDate)->diffInDays(Carbon::parse($data->endDate));
                    $dateDiff = $firstDayOfYear->diffInDays($lastDate);
                    $dateDiff = ($dateDiff)?$dateDiff:1;
                    $goal = ($data->goal/$activityDateDiff)*$dateDiff;
                  }
                  catch(\Exception $e){
                    $goal = $data->goal;
                  }

                endif;

                $goal = round($goal, 2);

              endif;

            endif;

          endif;

        endif;
        return $goal;
      }

    /**
    * getting goal indexes based on the start, end and tracking data
    * @param $date
    * @return int
    */
      public function getGoalIndexes($startDate, $endDate, $lastDate){

        $indexes = array();

        $startDate = (int)Carbon::parse($startDate)->format('m');
        $endDate = (Carbon::parse($endDate)->timestamp < Carbon::parse($lastDate)->timestamp)?$endDate:$lastDate;
        $endDate = (int)Carbon::parse($endDate)->format('m');
        $currentIndex = $startDate;

        $finalDate  = $startDate+12;
        for($count=$startDate;$count<=$finalDate;$count++){

          if($count >= (int)$currentIndex ){
            array_push($indexes, ($currentIndex-1));

            if($endDate == $currentIndex){
              return $indexes;
            }
            $currentIndex ++;
          }
          if($currentIndex == 13)
            $currentIndex = 1;
        }
        return $indexes;
      }
}



