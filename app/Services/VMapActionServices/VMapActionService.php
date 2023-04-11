<?php

namespace App\Services\VMapActionServices;

use DB;
use Auth;
use Lang;
use Config;
use Request;
use Carbon\Carbon;
use App\Models\DeletedItem;
use App\Traits\ApiResponse;
use App\Models\ActivityTitle;
use App\Models\Succession\Kpi;
use App\Services\FilterSystem;
use App\Models\Succession\VMap;
use App\Models\Succession\Value;
use App\Models\Succession\Project;
use App\Models\Succession\Strategy;
use App\Models\Succession\CriticalActivity;
use App\Services\VMapHelperServices\VMapHelpers;
use App\Services\VMapActionServices\VMapCopyLevelService;
use App\Http\Resources\vMapLevelUpdate\VMapLevelActivityUpdate;

class VMapActionService {

    use ApiResponse;

    private $filter;
    private $copyVmap;
    public function __construct()
    {
        $this->filter = new FilterSystem();
        $this->copyVmap = new VMapCopyLevelService();
        $this->helper = new VMapHelpers();
    }

	/**
	 *
	 * this function will save the new vMap data
	 * @param data
	 * @return object
	 */
	public function createNewVMap(array $data) {
		$dataArray = [];
		DB::beginTransaction();

			$vMap = new VMap();
			$vMap->formDate = $data['formDate'];
			$vMap->formTitle = $data['formTitle'];
			$vMap->userId = Auth::user()->user_id;
			$vMap->isDelete = true;
			$vMap->save();

			if ($vMap->vMapId) {
				$displayOrder = Value::select(DB::raw('Max(displayOrder) AS max'))->
					whereVmapid($vMap->vMapId)
					->first()->toArray();

				for ($incr = 1; $incr <= Config::get('constants.numberOfValue'); $incr++) {
					$value = new Value();

					$value->vMapId = $vMap->vMapId;
					$value->valueTitle = Config::get('statistics.valueDefaultTitle');
					$value->statusId = Config::get('statistics.vMapConstant.Pending');
					$value->isDelete = true;
					$value->valueUrl = 'null';
					$value->completed = 0;

					$value->displayOrder = ($displayOrder['max']) ? ($displayOrder['max'] + $incr) : $incr;
					$value->save();
				}

                //create default vmap activity titles
                ActivityTitle::create([
                    'vmvId' => $vMap->vMapId,
                    'valueTitle' => 'Value',
                    'kpiTitle' => 'Kpi',
                    'strategyTitle' => 'Strategy',
                    'projectTitle' => 'Project',
                    'caTitle' => 'Critical Activity',
                    'kActivityCheck' => false,
                    'sActivityCheck' => false,
                    'pActivityCheck' => false,
                    'cActivityCheck' => false,
                    'vmvId' => $vMap->vMapId
                ]);
				DB::commit();

				return $vMap;
			} else {

                return false;
			}
	}

	/**
	 * this funciton is used to update the
	 * vMap data
	 */
	public function updateNewVMap(array $data, $id) {
        $updateVmap = VMap::findOrFail($id);

        $updateVmap->formDate = $data['formDate'];
        $updateVmap->formTitle = $data['formTitle'];

        $updateVmap->save();

        return $updateVmap;

	}

	/**
	 *
	 * This function will delete the selected vMap
	 * @param NA
	 * @return object
	 */
	public function deleteVMap($id) {

		$messageArray = [];

        $getTitle = VMap::whereVmapid($id);

		if ($id) {
			if ($getTitle->update(['isDelete' => false])) {

                $formTitle = $getTitle->first();
				$deletedItem = new DeletedItem();
				$deletedItem->userId = Auth::user()->user_id;
				$deletedItem->tableName = 'vMap';
				$deletedItem->name = $formTitle->formTitle;
				$deletedItem->tableId = $id;
				$deletedItem->status = Config::get('constants.deletedItem');
				$deletedItem->save();
                return $this->successApiResponse(__('core.vMapDeleted'));
			} else {
                return $this->unprocessableApiResponse(__('core.vMapDeletedError'));
			}
		}else{
            return $this->unprocessableApiResponse(__('core.vMapDeletedError'));
        }
	}

	/**
	 * this function will update the vMap levels
	 *
	 * @param NA
	 * @return object
	 *
	 */
	public function vMapActivityUpdate(array $data, $levelId, $type) {

		$dataArray = [];
		$msgArray = [];
        switch ($type) {
        case 'level1':
            $values = array();
            $values['valueTitle'] = $data['name'];
            $values['statusId'] = $data['statusId'];
            $values['valueUrl'] = $data['url'];

            if (Value::whereValueid($levelId)->update($values)) {
                return $values;
            }

            break;
        case 'level2':
            $dataArray = self::formatVmapLevelCreate($data, $levelId);

            $dataArray['kpiName'] = $data['name'];
            if (Kpi::whereKpiid($levelId)->update($dataArray)) {
                return $dataArray;
            }
            break;

        case 'level3':
            $dataArray = self::formatVmapLevelCreate($data, $levelId);
            $dataArray['strategyName'] = $data['name'];
            if (Strategy::whereStrategyid($levelId)->update($dataArray)) {
                return $dataArray;
            }
            break;

        case 'level4':
            $dataArray = self::formatVmapLevelCreate($data, $levelId);
            $dataArray['projectName'] = $data['name'];
            if (Project::whereProjectid($levelId)->update($dataArray)) {
                return $dataArray;
            }
            break;

        case 'level5':
            $dataArray = self::formatVmapLevelCreate($data, $levelId);

            $dataArray['criticalActivityName'] = $data['name'];
            if (CriticalActivity::whereCriticalactivityid($levelId)->update($dataArray)) {
                return $dataArray;
            }
            break;
        }

	}

    /**
     * This function will delete the levels of vMap
     */
    public function getDeleteActivity(array $data, $id, $type)
    {
        if(!is_null($type)){
            $response = match($type){
                'level1' => Value::whereValueid($id)->update($data),
                'level2' => Kpi::whereKpiid($id)->update($data),
                'level3' => Strategy::whereStrategyid($id)->update($data),
                'level4' => Project::whereProjectid($id)->update($data),
                'level5' => CriticalActivity::whereCriticalactivityid($id)->update($data)
            };
        }

        // $response =
        return $response;
    }


    /**
     * This function will update the status of vmaps
     */
    public function statusUpdate($data, $id, $type)
    {

        $dataArray = [];

        $dataArray['statusId'] = $data;

        if($data == 2)
            $dataArray['completedDate'] = Carbon::now()->format('Y-m-d');
        else
            $dataArray['completedDate'] = Carbon::now()->format('Y-m-d');

        $response = self::getDeleteActivity($dataArray, $id, $type);


        return $response;
    }

    public function makeVMapCopy($data, $id)
    {


        DB::beginTransaction();
        try {
            $vMap = $this->helper->getVmapContent($id)->first();

            $vMapId = $this->copyVmap->copyVmap($vMap, $data);

            DB::commit();
            return $vMapId;

            DB::rollback();


        } catch (\Throwable $th) {

            dd($th);
            DB::rollback();
            return false;
        }
    }

    /**
     * This function will update the status of vmap whether to update it on the
     * dashboard or not
     *
     * @return bool
     */
    public function addToDashboard($vMapId, $value)
    {
        return VMap::whereVmapid($vMapId)->update(['showOnDashboard' => $value]);
    }

    /**
     * for creating the vmap levels
     *
     * @param [type] $data
     * @param [type] $type
     * @param [type] $levelId
     * @return void
     */
    public function createVmapLevels($data, $levelId, $type)
    {
        if (isset($type)) {
			switch ($type) {
			case 'level2':


				$dataArray = self::formatVmapLevelCreate($data, $levelId);
                $dataArray['kpiName'] = $data['name'];
                $dataArray['valueId'] = $levelId;

                $kpis = Kpi::create($dataArray);

                return $kpis;

			case 'level3':
				$dataArray = self::formatVmapLevelCreate($data, $levelId);
				$dataArray['strategyName'] = $data['name'];
                $dataArray['kpiId'] = $levelId;
				$strategy = Strategy::create($dataArray);

                return $strategy;

			case 'level4':
				$dataArray = self::formatVmapLevelCreate($data, $levelId);
				$dataArray['projectName'] = $data['name'];
                $dataArray['strategyId'] = $levelId;
				$project = Project::create($dataArray);

                return $project;

			case 'level5':
				$dataArray = self::formatVmapLevelCreate($data, $levelId);
				$dataArray['criticalActivityName'] = $data['name'];
                $dataArray['projectId'] = $levelId;
				$ca = CriticalActivity::create($dataArray);

                return $ca;

            default:
                return 'Level does not exist';
			}
		}
    }

    /**
     * for creating the vmap level 1
     *
     * @param [type] $data
     * @param [type] $id
     * @return void
     */
    public function createValues($data, $id)
    {
        $checkVmapExists = VMap::where('vMapId', $id)->exists();

        if($checkVmapExists) {
            $value = Value::create([
                'valueTitle' => $data['name'],
                'valueUrl' => $data['url'],
                'isDelete' => Config::get('constants.isDelete.true'),
                'statusId' => $data['status'],
                'vMapId' => $id,
                'completed' => false,
                'completedDate' => null
            ]);

            return $value;

        } else {
            return false;
        }
    }


    /**
     * formatting the vmap levels data
     *
     * @param [type] $data
     * @return void
     */
    public static function formatVmapLevelCreate($data, $levelId)
    {
        $dataArray['assignDate'] = Carbon::parse($data['assignDate'])->format(Config::get('constants.dbDateFormat'));
		$dataArray['dueDate'] = Carbon::parse($data['dueDate'])->format(Config::get('constants.dbDateFormat'));
		$dataArray['daily'] = $data['daily'] ?? 0;
		$dataArray['weekly'] = $data['weekly'] ?? 0;
		$dataArray['monthly'] = $data['monthly'] ?? 0;
		$dataArray['quarterly'] = $data['quarterly'] ?? 0;
		$dataArray['annually'] = $data['annually'] ?? 0;
		$dataArray['goal'] = $data['goal'] ?? 0;
		$dataArray['delegateTo'] = $data['delegateTo'] ? setDelegate($data['delegateTo']) : Auth::user()->user_id;
		// $dataArray['qualifierTo'] = $data['qualifierTo'] ? setDelegate($data['qualifierTo']) : '';
		$dataArray['seasonalGoal'] = $data['seasonalGoal'] ? implode(',', $data['seasonalGoal']) : 0;
		$dataArray['showOnDashboard'] = $data['showOnDashboard'] ?? false;
		$dataArray['includeInReporting'] = $data['includeInReporting'] ?? false;
		$dataArray['includeInAvatar'] = $data['includeInAvatar'] ?? false;
		$dataArray['includeInProfile'] = $data['includeInProfile'] ?? false;
		$dataArray['trackSign'] = $data['trackSign'] ?? '';
		$dataArray['seasonalSign'] = $data['seasonalSign'] ?? '';
		$dataArray['successScale'] = ($data['successScale']) ?? 0;
		$dataArray['statusId'] = $data['statusId'] ?? false;
		$dataArray['description'] = $data['description'] ?? '';
		$dataArray['isAccumulate'] = isset($data['isAccumulate']) ? $data['isAccumulate'] : 0;
        $dataArray['url'] = $data['url'] ?? '';

		if ($data['statusId'] == 2) {
			$dataArray['completedDate'] = Carbon::now()->format('Y-m-d');
		} else {
			$dataArray['completedDate'] = $data['assignDate'];
		}

        return $dataArray;
    }


}
