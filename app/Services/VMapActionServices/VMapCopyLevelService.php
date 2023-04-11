<?php

namespace App\Services\VMapActionServices;
use App\Models\Succession\Kpi;
use App\Models\Succession\Strategy;
use App\Models\Succession\Value;
use App\Models\Succession\VMap;
use App\Models\Succession\Project;
use App\Models\Succession\CriticalActivity;
use App\Services\VMapHelperServices\VMapHelpers;
use Carbon\Carbon;
use Config;
use DB;

class VMapCopyLevelService {

	/**
	 * This function will make the copy of the vMap
	 * @return object
	 *
	 */
	public function copyVmap($data, $postData) {

        $data = $data->toArray();

		foreach ($data['values'] as $key => $level) {
			$vmap = new VMap();

			$vmap->formTitle = $postData['formTitle'];
			$vmap->formDate = Carbon::parse($postData['formDate'])->format(Config::get('constants.dbDateFormat'));
			$vmap->userId = $postData['delegate'];
			$vmap->visionStatement = $data['visionStatement'];
			$vmap->missionStatement = $data['missionStatement'];
			$vmap->showOnDashboard = $data['showOnDashboard'];

			if ($vmap->save()) {
                self::setLevel1Copy($vmap->vMapId, $data['values']);

                return $vmap->vMapId;
			} else {
				return false;
			}
		}
	}

	/**
	 * This function will copy the level 1 data
	 * @param $vMapId, $vMapData as $data
	 * @return object
	 *
	 */
	public static function setLevel1Copy($vMapId, $data) {


		if ($data) {

            foreach ($data as $key => $values) {
                $level1 = new Value();
				$level1->vMapId = $vMapId;
				$level1->valueTitle = $values['valueTitle'];
				$level1->displayOrder = $values['displayOrder'];
				$level1->valueStatement = $values['valueStatement'];
				$level1->isDelete = true;
				$level1->valueUrl = $values['valueUrl'];
				$level1->completed = $values['completed'];
				$level1->statusId = $values['statusId'];
				$level1->completedDate = Carbon::parse($values['completedDate'])->format(Config::get('constants.dbDateFormat'));

				if ($level1->save()) {
					self::setLevel2Copy($level1['valueId'], $values);
				}

			}
            return true;
		}
		return false;
	}

	/**
	 * This function will copy the level2 data
	 */
	public static function setLevel2Copy($valueId, $data) {
		$data = $data['kpis'];
		if (count($data)) {
			foreach ($data AS $key => $kpis) {
                $level2 = new Kpi();
				$level2 = VMapHelpers::vMapLevelActivityUpdate($level2, $kpis);
				$level2->kpiName = $kpis['kpiName'];
				$level2->valueId = $valueId;

				if ($level2->save()) {
					self::setLevel3Copy($level2->kpiId, $kpis);
				}
			}
		}
	}

	/**
	 * This function will copy the level3 of the vmap
	 * @param kpiId, strategydata
	 */
	public static function setLevel3Copy($kpiId, $data) {
		$data = $data['strategy'];

		if (count($data)) {
            foreach ($data as $key => $strategy) {
				$level3 = new Strategy();
				$level3 = VMapHelpers::vMapLevelActivityUpdate($level3, $strategy);
				$level3->kpiId = $kpiId;
				$level3->strategyName = $strategy['strategyName'];

				if ($level3->save()) {
					self::setLevel4Copy($level3->strategyId, $strategy);
				}

			}
		}
	}

	/**
	 * This function will copy the level4 of the vmap
	 * @param strategyId, projectdata
	 * @return object
	 */
	public static function setLevel4Copy($strategyId, $data) {
		$data = $data['project'];

        if(count($data)){
            foreach ($data as $key => $project) {
                $level4 = new Project();
                $level4 = VMapHelpers::vMapLevelActivityUpdate($level4, $project);
                $level4->strategyId = $strategyId;
                $level4->projectName = $project['projectName'];

                if($level4->save()) {
                    self::setLevel5Copy($level4->projectId, $project);
                }
            }
        }
	}

    /**
     * This function will copy the level5 of the vmap
     * @param projectId
     * @param $data
     * @return bool
     */
    public static function setLevel5Copy($projectId, $data)
    {
        if(isset($data)){
            $data = $data['critical_activity'];
            foreach ($data as $key => $ca) {
                $level5 = new criticalActivity();
                $level5 = VMapHelpers::vMapLevelActivityUpdate($level5, $ca);
                $level5->projectId = $projectId;
                $level5->criticalActivityName = $ca['criticalActivityName'] ?? '';

                $level5->save();
            }
        }
        return false;
    }
}
