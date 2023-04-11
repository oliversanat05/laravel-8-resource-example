<?php

namespace App\Services\TrackingService;
use DB;
use Auth;
use Lang;
use Config;
use Carbon\Carbon;
use App\Models\Succession\VMap;
use App\Models\Tracking\TrackingData;
use App\Models\Tracking\AutoTrackingData;
use App\Models\Tracking\AutoTrackingDataDetails;

class TrackingService {

	public static function callArrangeData($params = null) {
		$dataArray = [];
		$incr = 0;
		$level = 0;
		$vMapUrl = '';

		$response = self::calUserTrackingData($params);

		if ($response) {
			foreach ($response as $value) {
				$dataArray[$value['Id']]['vmapLink']['value']['title'] = $value['valueTitle'];
				$dataArray[$value['Id']]['vmapLink']['value']['level'] = 1;
				$dataArray[$value['Id']]['vmapLink']['value']['path'] = $vMapUrl . '/level1/' . $value['level1'];

				$dataArray[$value['Id']]['vmapLink']['kpi']['title'] = isset($value['parent1']) ? $value['parent1'] : $value['name'];
				$dataArray[$value['Id']]['vmapLink']['kpi']['level'] = 2;
				$dataArray[$value['Id']]['vmapLink']['kpi']['path'] = $vMapUrl . '/level2/' . $value['level2'];
				$level	= 2;

				if ($value['tableName'] == 'strategy' || $value['tableName'] == 'project' || $value['tableName'] == 'criticalActivity'){

					$dataArray[$value['Id']]['vmapLink']['strategy']['title'] = isset($value['parent2']) ? $value['parent2'] : $value['name'];
					$dataArray[$value['Id']]['vmapLink']['strategy']['level'] = 3;
					$dataArray[$value['Id']]['vmapLink']['strategy']['path'] = $vMapUrl . '/level3/' . $value['level3'];
					$level	= 3;

					if ($value['tableName'] == 'project' || $value['tableName'] == 'criticalActivity'){

						$dataArray[$value['Id']]['vmapLink']['project']['title'] = isset($value['parent3']) ? $value['parent3'] : $value['name'];
						$dataArray[$value['Id']]['vmapLink']['project']['level'] = 4;
						$dataArray[$value['Id']]['vmapLink']['project']['path'] = $vMapUrl . '/level4/' . $value['level4'];
						$level	= 4;

						if ($value['tableName'] == 'criticalActivity'){

							$dataArray[$value['Id']]['vmapLink']['ca']['title'] = isset($value['parent4']) ? $value['parent4'] : $value['name'];
							$dataArray[$value['Id']]['vmapLink']['ca']['level'] = 5;
							$dataArray[$value['Id']]['vmapLink']['ca']['path'] = $vMapUrl . '/level5/' . $value['level5'];
							$level	= 5;

                        }
                    }
                }

				$dataArray['value'][$value['valueId']]['id'] = $value['valueId'];
				$dataArray['value'][$value['valueId']]['title'] = $value['valueTitle'];
				$dataArray['value'][$value['valueId']]['vMapId'] = $value['vMapId'];

				$dataArray['activity'][$value['Id']]['id'] = $value['Id'];
				$dataArray['activity'][$value['Id']]['title'] = $value['name'];
				$dataArray['activity'][$value['Id']]['vMapId'] = $value['vMapId'];
				$dataArray['activity'][$value['Id']]['valueId'] = $value['valueId'];
				$dataArray['activity'][$value['Id']]['type'] = $value['tableName'];

				$dataArray[$value['Id']]['raw'] = implode('-', [$value['vMapId'], $value['valueId'], $value['delegateTo'], $value['status'], $value['Id']]);

				$dataArray[$value['Id']]['data'][$incr]['trackingId'] = $value['trackingDataId'];
				$dataArray[$value['Id']]['data'][$incr]['beginningDate'] = Carbon::parse($value['startDate'])->format(Config::get('constants.dateFormat'));
				$dataArray[$value['Id']]['data'][$incr]['endingDate'] = Carbon::parse($value['endDate'])->format(Config::get('constants.dateFormat'));
				$dataArray[$value['Id']]['data'][$incr]['data'] = $value['trackingValue'];
				$dataArray[$value['Id']]['data'][$incr]['comment'] = (string) ($value['comment']);
				$dataArray[$value['Id']]['data'][$incr]['activity'] = $value['tableName'];
				$dataArray[$value['Id']]['data'][$incr]['activityId'] = $value['Id'];
				$dataArray[$value['Id']]['data'][$incr]['assignDate'] = $value['assignDate'];
				$dataArray[$value['Id']]['data'][$incr++]['dueDate'] = $value['dueDate'];

				$dataArray[$value['Id']]['status'] = $value['status'];
				$dataArray[$value['Id']]['type'] = $value['tableName'];
				$dataArray[$value['Id']]['level'] = $level;
				$dataArray[$value['Id']]['activityId'] = $value['Id'];
			}

		}

		return $dataArray;

	}

	public static function setSerializeArray($params = null) {
		$megaArray = [];
		$returnArray = [];
		$middleArray = [];
		$dropArray['vMap'] = [];
		$dropArray['value'] = [];
		$dropArray['activity'] = [];

		$dataArray = self::callArrangeData($params);

		foreach ($dataArray as $index => $value) {
			$middleArray['vmapLink'] = array();
			$middleArray['data'] = array();
			$middleArray['status'] = array();
			$middleArray['raw'] = array();

			foreach ($value as $key => $val) {
				if ($index == 'value') {
					array_push($dropArray[$index], $val);
				}
				if ($key == 'raw') {
					$middleArray[$key] = $val;
				}
				if ($index == 'activity') {
					array_push($dropArray[$index], $val);
				}
				if ($key == 'vmapLink') {
					foreach ($val as $unique => $link) {
						array_push($middleArray[$key], $link);
					}
				}

				if ($key == 'status') {
					$middleArray['status'] = $val;
				}
				if ($key == 'type') {
					$middleArray['type'] = $val;
				}
				if ($key == 'level') {
					$middleArray['level'] = $val;
				}
				if ($key == 'activityId') {
					$middleArray[$key] = $val;
				}
				if ($key == 'data') {
					foreach ($val as $unique => $data) {
						array_push($middleArray['data'], $data);
					}
				}
			}

			if (count($middleArray['vmapLink'])) {
				array_push($megaArray, $middleArray);
			}
		}

		$filterData  = self::callFilterData($params);

		$dropArray['vMap'] = array_unique($dropArray['vMap']);
		$returnArray['data'] = $megaArray;
		$returnArray['dropdown'] = $filterData;
		$returnArray['authUser'] = Auth::user()->profile;
		$returnArray['params']= $params;
		return $returnArray;
	}

	public static function callFilterData($params)
	{
		$params['status'] = '';
		$dataArray = self::callArrangeData($params);
		$dropArray['vMap'] = [];
		$dropArray['value'] = [];
		$dropArray['activity'] = [];

		foreach ($dataArray as $index => $value) {
			$middleArray['vmapLink'] = array();
			$middleArray['data'] = array();
			$middleArray['status'] = array();
			$middleArray['raw'] = array();

			foreach ($value as $key => $val) {
				if ($index == 'value') {
					array_push($dropArray[$index], $val);
				}
				if ($index == 'activity') {
					array_push($dropArray[$index], $val);
				}
				if ($key == 'activityId') {
					$middleArray[$key] = $val;
				}
			}

			if (count($middleArray['vmapLink'])) {
				array_push($megaArray, $middleArray);
			}
		}

		return $dropArray;
	}

	/**
	 * This function will get the tracking data for kpi table having vMap and value parents.
	 * @param NA
	 * @return object
	 */
	public static function calUserTrackingData($params = null) {
		$array = ['vMap.userId', 'vMap.formTitle', 'trackingDataId', 'vMap.vMapId', 'k.kpiId as Id', 'k.referenceId as referenceId', DB::raw('"kpi" AS tableName, kpiName as parent1, "strategy" AS parent2, "project" AS parent3, "criticalActivity" AS parent4, v.valueId AS level1,k.kpiId AS level2, "strategy" AS level3,"project" AS level4, "CA" AS level5'),
			'k.statusId as status', 'k.delegateTo as delegateTo', 'trackingDate', 'startDate', 'endDate', 'trackingValue', 'comment', 'v.valueId', '.valueTitle', 'kpiName AS name', 'k.assignDate as assignDate', 'k.dueDate as dueDate'];

		return VMap::select($array)
			->leftjoin('value AS v', 'v.vMapId', '=', 'vMap.vMapId')
			->leftjoin('kpi AS k', 'k.valueId', '=', 'v.valueId')
			->leftjoin('trackingData', 'trackingData.kpiId', '=', 'k.kpiId')
			->where('k.includeInReporting', true)
			->where('userId', Auth::user()->user_id)
			->where('k.isDelete', false)
			->when($params['vMap'] == null,
			function ($q) use($params){
				return $q->whereNotNull('vMap.vMapId');
			},
			function ($q) use($params){
				return $q->whereIn('vMap.vMapId', $params['vMap']);
			})
			->where('vMap.isDelete', true)->where('v.isDelete', true)
			->when($params['value'] == null,
			function ($q) use($params){
				return $q->whereNotNull('v.valueId');
			},
			function ($q) use($params){
				return $q->whereIn('v.valueId', $params['value']);
			})
			->when($params['kpi'] == null,
			function ($q) use($params){
				return $q->whereNotNull('k.kpiId');
			},
			function ($q) use($params){
				return $q->whereIn('k.kpiId', $params['kpi']);
			})
			->when($params['delegate'] == null,
			function ($q) use($params){
				return $q->whereNotNull('k.delegateTo');
			},
			function ($q) use($params){
				return $q->whereIn('k.delegateTo', $params['delegate']);
			})
			->when(empty($params['status']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->whereIn('k.statusId', $params['status']);
			})
			->when(empty($params['beginingDate']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->where('trackingData.startDate', '>=',  $params['beginingDate'])->Where('trackingData.endDate', '<=', $params['endingDate']);
			})
			->union(self::calUserStrategyData($params))
			->union(self::calUserProjectData($params))
			->union(self::calUserCriticalActivityData($params))
			->orderBy('valueTitle', 'ASC')
			->orderBy('parent1', 'ASC')
			->orderBy('parent2', 'ASC')
			->orderBy('parent3', 'ASC')
			->orderBy('startDate', 'DESC')
			->get()->toArray();
		return $vmap;
	}

	/**
	 * This function will get the tracking data for strategy table having vMap and value parents.
	 * @param NA
	 * @return object
	 */
	public static function calUserStrategyData($params = null) {
		$array = ['vMap.userId', 'vMap.formTitle', 'trackingDataId', 'vMap.vMapId', 's.strategyId as Id', 's.referenceId as referenceId', DB::raw('"strategy" AS tableName, kpiName As parent1, strategyName as parent2, "project" AS parent3, "criticalActivity" AS parent4, v.valueId AS level1,k.kpiId AS level2, s.strategyId AS level3, "project" AS level4,"CA" AS level5'),
			's.statusId as status', 's.delegateTo as delegateTo', 'trackingDate', 'startDate', 'endDate', 'trackingValue', 'comment', 'v.valueId', '.valueTitle', 'strategyName AS name', 's.assignDate as assignDate', 's.dueDate as dueDate'];

		return VMap::select($array, "strategy AS tableName")
			->leftjoin('value AS v', 'v.vMapId', '=', 'vMap.vMapId')
			->leftjoin('kpi AS k', 'k.valueId', '=', 'v.valueId')
			->leftjoin('strategy AS s', 's.kpiId', '=', 'k.kpiId')
			->leftjoin('trackingData', 'trackingData.strategyId', '=', 's.strategyId')
			->where('s.includeInReporting', true)->where('s.isDelete', false)
			->where('userId', Auth::user()->user_id)
			->where('vMap.isDelete', true)->where('v.isDelete', true)
			->when($params['vMap'] == null,
			function ($q) use($params){
				return $q->whereNotNull('vMap.vMapId');
			},
			function ($q) use($params){
				return $q->whereIn('vMap.vMapId', $params['vMap']);
			})
			->when($params['value'] == null,
			function ($q) use($params){
				return $q->whereNotNull('v.valueId');
			},
			function ($q) use($params){
				return $q->whereIn('v.valueId', $params['value']);
			})
			->when($params['kpi'] == null,
			function ($q) use($params){
				return $q->whereNotNull('k.kpiId');
			},
			function ($q) use($params){
				return $q->whereIn('k.kpiId', $params['kpi']);
			})
			->when($params['activity'] == null,
			function ($q) use($params){
				return $q->whereNotNull('s.strategyId');
			},
			function ($q) use($params){
				return $q->whereIn('s.strategyId', $params['activity']);
			})
			->when($params['delegate'] == null,
			function ($q) use($params){
				return $q->whereNotNull('s.delegateTo');
			},
			function ($q) use($params){
				return $q->whereIn('s.delegateTo', $params['delegate']);
			})
			->when(empty($params['status']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->whereIn('s.statusId', $params['status']);
			})
			->when(empty($params['beginingDate']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->where('trackingData.startDate', '>=',  $params['beginingDate'])->Where('trackingData.endDate', '<=', $params['endingDate']);
			});
	}

	/**
	 * This function will get the tracking data for strategy table having vMap and value parents.
	 * @param NA
	 * @return object
	 */
	public static function calUserProjectData($params = null) {
		$array = ['vMap.userId', 'vMap.formTitle', 'trackingDataId', 'vMap.vMapId', 'p.projectId as Id', 'p.referenceId as referenceId', DB::raw('"project" AS tableName, kpiName AS parent1, strategyName AS parent2, projectName as parent3,"criticalActivity" AS parent4, v.valueId AS level1,k.kpiId AS level2, s.strategyId AS level3, p.projectId AS level4, "CA" AS level5'),
			'p.statusId as status', 'p.delegateTo as delegateTo', 'trackingDate', 'startDate', 'endDate', 'trackingValue', 'comment', 'v.valueId', '.valueTitle', 'projectName AS name', 'p.assignDate as assignDate', 'p.dueDate as dueDate'];

		return VMap::select($array, "project AS tableName")
			->leftjoin('value AS v', 'v.vMapId', '=', 'vMap.vMapId')
			->leftjoin('kpi AS k', 'k.valueId', '=', 'v.valueId')
			->leftjoin('strategy AS s', 's.kpiId', '=', 'k.kpiId')
			->leftjoin('project AS p', 'p.strategyId', '=', 's.strategyId')
			->leftjoin('trackingData', 'trackingData.projectId', '=', 'p.projectId')
			->where('p.includeInReporting', true)->where('p.isDelete', false)
			->where('userId', Auth::user()->user_id)
			->where('vMap.isDelete', true)->where('v.isDelete', true)
			->when($params['vMap'] == null,
			function ($q) use($params){
				return $q->whereNotNull('vMap.vMapId');
			},
			function ($q) use($params){
				return $q->whereIn('vMap.vMapId', $params['vMap']);
			})
			->when($params['value'] == null,
			function ($q) use($params){
				return $q->whereNotNull('v.valueId');
			},
			function ($q) use($params){
				return $q->whereIn('v.valueId', $params['value']);
			})
			->when($params['kpi'] == null,
			function ($q) use($params){
				return $q->whereNotNull('k.kpiId');
			},
			function ($q) use($params){
				return $q->whereIn('k.kpiId', $params['kpi']);
			})
			->when($params['activity'] == null,
			function ($q) use($params){
				return $q->whereNotNull('p.projectId');
			},
			function ($q) use($params){
				return $q->whereIn('p.projectId', $params['activity']);
			})
			->when($params['delegate'] == null,
			function ($q) use($params){
				return $q->whereNotNull('p.delegateTo');
			},
			function ($q) use($params){
				return $q->whereIn('p.delegateTo', $params['delegate']);
			})
			->when(empty($params['status']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->whereIn('p.statusId', $params['status']);
			})
			->when(empty($params['beginingDate']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->where('trackingData.startDate', '>=',  $params['beginingDate'])->Where('trackingData.endDate', '<=', $params['endingDate']);
			});
	}

	/**
	 * This function will get the tracking data for critical Activity table having vMap and value parents.
	 * @param NA
	 * @return object
	 */
	public static function calUserCriticalActivityData($params=null) {
		$array = ['vMap.userId', 'vMap.formTitle', 'trackingDataId', 'vMap.vMapId', 'c.criticalActivityId as Id', 'c.referenceId as referenceId',
			DB::raw('
                    "criticalActivity" AS tableName,
                    strategyName AS parent2,
                    projectName AS parent3,
                    criticalActivityName as parent4,
                    kpiName AS parent1,
                    v.valueId AS level1,
                    k.kpiId AS level2,
                    s.strategyId AS level3,
                    p.projectId AS level4,
                    c.criticalActivityId AS level5'
			),
			'c.statusId as status', 'c.delegateTo as delegateTo', 'trackingDate', 'startDate', 'endDate', 'trackingValue', 'comment', 'v.valueId', '.valueTitle', 'criticalActivityName as name', 'c.assignDate as assignDate', 'c.dueDate as dueDate'];

		return VMap::select($array)
			->leftjoin('value AS v', 'v.vMapId', '=', 'vMap.vMapId')
			->leftjoin('kpi AS k', 'k.valueId', '=', 'v.valueId')
			->leftjoin('strategy AS s', 's.kpiId', '=', 'k.kpiId')
			->leftjoin('project AS p', 'p.strategyId', '=', 's.strategyId')
			->leftjoin('criticalActivity AS c', 'p.projectId', '=', 'c.projectId')
			->leftjoin('trackingData', 'trackingData.criticalActivityId', '=', 'c.criticalActivityId')
			->where('c.includeInReporting', true)->where('c.isDelete', false)
			->where('userId', Auth::user()->user_id)
			->where('vMap.isDelete', true)->where('v.isDelete', true)
			->when($params['vMap'] == null,
			function ($q) use($params){
				return $q->whereNotNull('vMap.vMapId');
			},
			function ($q) use($params){
				return $q->whereIn('vMap.vMapId', $params['vMap']);
			})
			->when($params['value'] == null,
			function ($q) use($params){
				return $q->whereNotNull('v.valueId');
			},
			function ($q) use($params){
				return $q->whereIn('v.valueId', $params['value']);
			})
			->when($params['kpi'] == null,
			function ($q) use($params){
				return $q->whereNotNull('k.kpiId');
			},
			function ($q) use($params){
				return $q->whereIn('k.kpiId', $params['kpi']);
			})
			->when($params['activity'] == null,
			function ($q) use($params){
				return $q->whereNotNull('c.criticalActivityId');
			},
			function ($q) use($params){
				return $q->whereIn('c.criticalActivityId', $params['activity']);
			})
			->when($params['delegate'] == null,
			function ($q) use($params){
				return $q->whereNotNull('p.delegateTo');
			},
			function ($q) use($params){
				return $q->whereIn('p.delegateTo', $params['delegate']);
			})
			->when(empty($params['status']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->whereIn('c.statusId', $params['status']);
			})
			->when(empty($params['beginingDate']),
			function ($q) use($params){
				return $q;
			},
			function ($q) use($params){
				return $q->where('trackingData.startDate', '>=',  $params['beginingDate'])->Where('trackingData.endDate', '<=', $params['endingDate']);
			});
	}

	/**
	 * This function will add / edit the tracking records
	 * @param $data
	 * @return object
	 */
	public function setTrackingDataRecord($data) {

        $tracking = new TrackingData();
        $dataArray = $tracking->saveTrackData($data);

		return $dataArray;

	}

	/**
	 * This function will delete the tracking data
	 */
	public function deleteTrackingDataRecords($data) {
		$dataArray = [];

		if ($data) {
			$delete = TrackingData::deleteRecords($data);
			return true;
		} else {
			return false;
		}

	}

	public function unSerializeTrackingDataParams($params)
	{
		$paramsData = [];
		try {

			$statusFilter  = $this->statusFilter($params['status']);
			$paramsData['vMap']  	= $params['vMap'] ? explode(',', $params['vMap']) : '';
			$paramsData['value']  	= $params['value'] ? explode(',', $params['value']) : '';
			$paramsData['kpi']  	= $params['kpi'] ? explode(',', $params['kpi']) : '';
			$paramsData['delegate'] = $params['delegate'] ? explode(',', $params['delegate']) : '';
			$paramsData['status']  	= ($params['status'] != "") ? $statusFilter : "";
			$paramsData['activity'] = $params['activity'] ? explode(',', $params['activity']) : '';
			$paramsData['beginingDate'] = $params['beginingDate'];
			$paramsData['endingDate'] = $params['endingDate'];
			

		} catch (\Throwable $th) {

		}

		return $paramsData;
	}
    /**
     * insert the auto generated data
     *
     * @param $data
     * @return void
     */
	public function insertAutoGeneratedData($data)
    {
		$autoGeneratedData = [];

        $dataArray = [];
        $activityDataArray = [];

		$autoGeneratedData['user_id'] = Auth::user()->user_id;
		$autoGeneratedData['comment'] = is_null($data['comment']) ? 'System Generated' : $data['comment'];
		$autoGeneratedData['end_date'] = Carbon::parse($data['end_date'])->format(Config::get('constants.dbDateFormat'));

		$autoTrackingData = AutoTrackingData::create($autoGeneratedData);


        if($autoTrackingData) {
            foreach($data['trackingData'] as $value){
                if(!$value['endingDate'] || $value['endingDate'] == ''){
                    if(!$value['activityStartDate'] || $value['activityStartDate'] == ''){
                        $value['endingDate'] =  Carbon::parse($data['end_date'])->subDays(2);
                    }else{
                        $value['endingDate'] = Carbon::parse($date['end_date'])->subDay();
                    }
                }

                $getResponse = self::insertAutoTracking($value['endingDate'], $value, $data, $autoTrackingData['id']);

                if($getResponse) {
                    $dataArray[] = $getResponse;
                }

            }

            if(count($dataArray)) {
                $activityDataArray['user_id']       =  Auth::user()->user_id;
                $activityDataArray['autoInsertId']  =  $autoTrackingData['id'];
                $activityDataArray['activity_data'] = json_encode($dataArray);
                // Insert all data details for user.
                AutoTrackingDataDetails::create($activityDataArray);

                return $activityDataArray;
            } else {
                return false;
            }
        }
    }

    /**
     * undo the tracking data delete
     *
     * @param $trackingId
     * @return void
     */
    public function deleteSystemGeneratedData($trackingId)
    {
        if($trackingId) {

            TrackingData::where('autoInsertId', $trackingId)->where('associated_user', Auth::user()->user_id)->where('tracking_type', true)->delete();
            AutoTrackingData::where('user_id', Auth::user()->user_id)->whereId($trackingId)->delete();

            AutoTrackingDataDetails::where('user_id', Auth::user()->user_id)->where('autoInsertId',$trackingId)->delete();

            return true;
        } else {
            return false;
        }



    }

    /**
     * status filter for tracking data
     */
	public function statusFilter($status)
	{
		try {
			$response = [];
			$replacement 	= config('constants.TRACKING_DATA_GROUP_FILTER_REPLACE');
			$groupFilter    = config('constants.TRACKING_DATA_GROUP_FILTER');

			if( $status == $groupFilter )
			{
				foreach ($replacement as $index => $data) {
					array_push($response, $data);
				}
			}
			else{
				$response[0] = $status;
			}

			return $response;
		} catch (\Throwable $th) {
		}
	}

    /**
     * insert the autotracking data
     */
    public function insertAutoTracking($trackingDate, $getValue, $getRequest, $getLastId){

        // dd($trackingDate, $getValue, $getRequest, $getLastId);

        // Check if any activity exist.
        $dataArray = array();
        $trackingData = array();

        if($getValue){
            $newTrackingNextDate = Carbon::parse($getRequest['end_date'])->format('Y-m-d');
            $trackingDate        = Carbon::parse($trackingDate)->format('Y-m-d');

            if(Carbon::parse($newTrackingNextDate)->gt($trackingDate)){
                $newTrackingDate = Carbon::parse($trackingDate)->addDay();

                $newTrackingNextDate = Carbon::parse($getRequest['end_date'])->format('Y-m-d');
                $newTrackingDate = Carbon::parse($newTrackingDate)->format(Config::get('constants.dbDateFormat'));
                $newTrackingNextDate = Carbon::parse($newTrackingNextDate)->format(Config::get('constants.dbDateFormat'));
                $dataArray['trackingDate'] = Carbon::now()->format('Y-m-d');
                $dataArray['trackingValue'] = $getRequest['data'];
                $dataArray['tracking_type'] = true;
                $dataArray['associated_user'] = Auth::user()->user_id;
                $dataArray['startDate'] = $newTrackingDate;
                $dataArray['endDate'] = $newTrackingNextDate;
                $dataArray['comment'] = is_null($getRequest['comment']) ? 'System Generated' : $getRequest['comment'];
                $dataArray['autoInsertId'] = $getLastId;
                $activityType = $getValue['type'] . 'Id';
                $dataArray[$activityType] = $getValue['id'];
                $trackingData['value'] = $getRequest['data'];
                $trackingData['type'] = $getValue['type'];
                $trackingData['id'] = $getValue['id'];

                // Insert automated system tracking data for activity
                TrackingData::create($dataArray);
                return $trackingData;
            } else{
                return false;
            }

        }
    }
}
