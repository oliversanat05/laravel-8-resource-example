<?php

namespace App\Services\VMapActionServices;

use App\Models\Succession\CriticalActivity;
use App\Models\Succession\Kpi;
use App\Models\Succession\Project;
use App\Models\Succession\Strategy;
use App\Models\Succession\Value;
use Request;

class VMapUpdateOrderService {

	/**
	 * This function will update the order of the vmap and its
	 * children
	 */
	public function updateVmapOrder() {
		$node = Request::get('node');
		$parent = Request::get('parent');
		$indexArray = Request::get('index');

		$response = '';

		try {
			$response = match($node['type']) {
				'level1' => $this->updateValueOrder($node, $parent, $indexArray),
				'level2' => $this->updateKpiOrder($node, $parent, $indexArray),
				'level3' => $this->updateStrategyOrder($node, $parent, $indexArray),
                'level4' => $this->updateProjectOrder($node, $parent, $indexArray),
                'level5' => $this->updateCriticalActivityOrder($node, $parent, $indexArray)
			};

			return $response;

		} catch (\Exception$th) {
			echo $th->getMessage();
			return response()->json([
				'success' => false,
				'message' => 'Oops, something went wrong',
			], 401);
		}

		return null;

	}

	/**
	 * This function will update the value order of the vmap
	 * @param $node
	 * @param $parent
	 * @param $indexArray
	 * @return bool
	 */
	public function updateValueOrder($node, $parent, $indexArray) {
		try {

			foreach ($indexArray as $key => $valueId) {
				Value::whereValueid($valueId)->update(['displayOrder' => $key + 1]);
			}

			return true;
		} catch (\Throwable$th) {
            dd($th);
			return false;
		}
	}

	/**
	 * This function will update the kpi order of the vmap
	 * @param $node
	 * @param $parent
	 * @param $indexArray
	 *
	 * @return bool
	 */
	public function updateKpiOrder($node, $parent, $indexArray) {
		try {
			if ($node['parent'] != $parent['Id']) {
				Kpi::whereKpiid($node['Id'])->update(['valueId' => $parent['Id']]);
			}

			foreach ($indexArray as $key => $kpiId) {
				Kpi::whereKpiid($kpiId)->update(['kOrder' => $key + 1]);
			}

			return true;
		} catch (\Exception$th) {
			return false;
		}

		return false;
	}

	/**
	 * This function will update the strategy order of the vmap
	 *
	 * @param $node
	 * @param $parent
	 * @param $indexArray
	 *
	 * @return bool
	 */
	public function updateStrategyOrder($node, $parent, $indexArray) {
		try {
			if ($node['parent'] != $parent['Id']) {
				Strategy::whereStrategyid($node['Id'])->update(['KpiId' => $parent['Id']]);
			}

			foreach ($indexArray as $key => $strategyId) {
				Strategy::whereStrategyid($strategyId)->update(['sOrder' => $key + 1]);
			}

			return true;
		} catch (\Throwable $th) {
			return false;
		}

		return false;
	}

	/**
	 * This function will update the project order of the vmap
	 *
	 * @param $node
	 * @param $parent
	 * @param $indexArray
	 *
	 * @return bool
	 */
	public function updateProjectOrder($node, $parent, $indexArray) {
		try {
			if ($node['parent'] != $parent['Id']) {
				Project::whereProjectid($node['Id'])->update(['strategyId' => $parent['Id']]);
			}
			foreach ($indexArray as $key => $projectId) {
				Project::whereProjectid('projectId', $projectId)->update(['pOrder' => $key + 1]);
			}

			return true;
		} catch (\Exception$th) {
			return false;
		}
		return false;
	}

	/**
	 * This function will update the project order of the vmap
	 *
	 * @param $node
	 * @param $parent
	 * @param $indexArray
	 *
	 * @return bool
	 */
	public function updateCriticalActivityOrder($node, $parent, $indexArray) {
		try {
			if ($node['parent'] != $parent['Id']) {
				CriticalActivity::whereCriticalactivityid($node['Id'])->update(['projectId' => $parent['Id']]);
			}
			foreach ($indexArray as $key => $criticalActivityId) {
				CriticalActivity::whereCriticalactivityid($criticalActivityId)->update(['cOrder' => $key + 1]);
			}
            return true;
		} catch (Exception $e) {
			return false;
		}
        return false;
	}

}
