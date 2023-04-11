<?php

namespace App\Services\NotificationService;

class LevelsActivityService {

    public function __contruct()
    {
        $this->model = new CommunicationOption();
    }

    /**
     * this function get the level 2 activity
     *
     * @param $data
     * @param $alertData
     * @return void
     */
	public function getLevel2Activty($data, $alertData) {

		$dataArray = array();
		$arrays = array();

		if (!empty($data['kpiId'])) {

			$delegate = ($data['kDelegateTo']) ? explode(',', $data['kDelegateTo']) : array('0' => $data['userId']);

			foreach ($delegate AS $key => $val) {

				if (in_array($val, $alertData)) {

					$dataArray[$data['kDueDate']]['type'] = 'kpi';
					$dataArray[$data['kDueDate']]['userId'] = $data['userId'];
					$dataArray[$data['kDueDate']]['parent'] = $data['valueTitle'];
					$dataArray[$data['kDueDate']]['id'] = $data['kpiId'];
					$dataArray[$data['kDueDate']]['name'] = $data['kpiName'];
					$dataArray[$data['kDueDate']]['status'] = $data['kStatusId'];
					$dataArray[$data['kDueDate']]['dueDate'] = $data['kDueDate'];
					$dataArray[$data['kDueDate']]['delegate'] = ($data['kDelegateTo']) ? $data['kDelegateTo'] : $val;
					$dataArray[$data['kDueDate']]['includeInAvatar'] = $data['kIncludeInAvatar'];
					$dataArray[$data['kDueDate']]['includeInProfile'] = $data['kIncludeInProfile'];
					$dataArray[$data['kDueDate']]['completed'] = $data['kCompleted'];
					$arrays[] = $dataArray;
				}
			}
			return count($arrays) ? array_filter($arrays) : false;
		}
	}

    /**
     * this function get the level 3 activity
     *
     * @param $data
     * @param $alertData
     * @return void
     */
	public function getLevel3Activty($data, $alertData) {

		$dataArray = array();
		$arrays = array();

		if (!empty($data['strategyId'])) {

			$delegate = ($data['sDelegateTo']) ? explode(',', $data['sDelegateTo']) : array('0' => $data['userId']);

			foreach ($delegate AS $key => $val) {

				if (in_array($val, $alertData)) {

					$dataArray[$data['sDueDate']]['type'] = 'strategy';
					$dataArray[$data['sDueDate']]['userId'] = $data['userId'];
					$dataArray[$data['sDueDate']]['parent'] = '<b>' . $data['valueTitle'] . '</b> >> ' . $data['kpiName'];
					$dataArray[$data['sDueDate']]['id'] = $data['strategyId'];
					$dataArray[$data['sDueDate']]['name'] = $data['strategyName'];
					$dataArray[$data['sDueDate']]['status'] = $data['sStatusId'];
					$dataArray[$data['sDueDate']]['dueDate'] = $data['sDueDate'];
					$dataArray[$data['sDueDate']]['delegate'] = ($data['sDelegateTo']) ? $data['sDelegateTo'] : $val;
					$dataArray[$data['sDueDate']]['includeInAvatar'] = $data['sIncludeInAvatar'];
					$dataArray[$data['sDueDate']]['includeInProfile'] = $data['sIncludeInProfile'];
					$dataArray[$data['sDueDate']]['completed'] = $data['sCompleted'];

					$arrays[] = $dataArray;
				}
			}
		}
		return array_filter($arrays);
	}

    /**
     * this function get the level 4 activity
     *
     * @param $data
     * @param $alertData
     * @return void
     */
	public function getLevel4Activty($data, $alertData) {

		$dataArray = array();
		$arrays = array();

		if (!empty($data['projectId'])) {

			$delegate = ($data['pDelegateTo']) ? explode(',', $data['pDelegateTo']) : array('0' => $data['userId']);

			foreach ($delegate AS $key => $val) {

				if (in_array($val, $alertData)) {

					$dataArray[$data['pDueDate']]['type'] = 'project';
					$dataArray[$data['pDueDate']]['userId'] = $data['userId'];
					$dataArray[$data['pDueDate']]['parent'] = '<b>' . $data['valueTitle'] . '</b> >> ' . $data['kpiName'] . ' >> ' . $data['strategyName'];
					$dataArray[$data['pDueDate']]['id'] = $data['projectId'];
					$dataArray[$data['pDueDate']]['name'] = $data['projectName'];
					$dataArray[$data['pDueDate']]['status'] = $data['pStatusId'];
					$dataArray[$data['pDueDate']]['dueDate'] = $data['pDueDate'];
					$dataArray[$data['pDueDate']]['delegate'] = ($data['pDelegateTo']) ? $data['pDelegateTo'] : $val;
					$dataArray[$data['pDueDate']]['includeInAvatar'] = $data['pIncludeInAvatar'];
					$dataArray[$data['pDueDate']]['includeInProfile'] = $data['pIncludeInProfile'];

					//$dataArray[$data['pDueDate']]['checkIfCurrency']                = $data['pCheckIfCurrency'];
					$dataArray[$data['pDueDate']]['completed'] = $data['pCompleted'];

					$arrays[] = $dataArray;
				}
			}
		}
		return array_filter($arrays);
	}

    /**
     * this function get the level 5 activity
     *
     * @param $data
     * @param $alertData
     * @return void
     */
	public function getLevel5Activty($data, $alertData) {

		$dataArray = array();
		$arrays = array();

		if (!empty($data['criticalActivityId'])) {

			$delegate = ($data['cDelegateTo']) ? explode(',', $data['cDelegateTo']) : array('0' => $data['userId']);

			foreach ($delegate AS $key => $val) {

				if (in_array($val, $alertData)) {

					$dataArray[$data['cDueDate']]['type'] = 'criticalActivity';
					$dataArray[$data['cDueDate']]['userId'] = $data['userId'];
					$dataArray[$data['cDueDate']]['parent'] = $data['valueTitle'] . $data['kpiName'] . $data['strategyName'] . $data['projectName'];
					$dataArray[$data['cDueDate']]['id'] = $data['criticalActivityId'];
					$dataArray[$data['cDueDate']]['name'] = $data['criticalActivityName'];
					$dataArray[$data['cDueDate']]['status'] = $data['cStatusId'];
					$dataArray[$data['cDueDate']]['dueDate'] = $data['cDueDate'];
					$dataArray[$data['cDueDate']]['delegate'] = ($data['cDelegateTo']) ? $data['cDelegateTo'] : $val;
					$dataArray[$data['cDueDate']]['includeInAvatar'] = $data['cIncludeInAvatar'];
					//$dataArray[$data['cDueDate']]['checkIfCurrency']                = $data['cCheckIfCurrency'];
					$dataArray[$data['cDueDate']]['completed'] = $data['cCompleted'];
					$dataArray[$data['cDueDate']]['includeInProfile'] = $data['cIncludeInProfile'];

					$arrays[] = $dataArray;
				}
			}
		}
		return array_filter($arrays);
	}

    /**
     * this function get the level 2 dial activity
     *
     * @param $data
     * @param $alertDataVal
     * @return void
     */
    public function getLevel2DialActivty($data, $alertDataVal) {

		$dataArray = array();
		$arrays = array();

		if (!empty($data['kpiId']) || !empty($data['strategyId']) || !empty($data['projectId']) || !empty($data['criticalActivityId'])) {

			$delegate = ($data['delegateTo']) ? explode(',', $data['delegateTo']) : array('0' => $data['userId']);

			foreach ($delegate AS $key => $val) {

				if (in_array($val, $alertDataVal)) {

					$dataArray[$data['dueDate']]['type'] = $data['tableName'];
					$dataArray[$data['dueDate']]['userId'] = $data['userId'];
					$dataArray[$data['dueDate']]['parent'] = $data['valueTitle'];
					$dataArray[$data['dueDate']]['id'] = $data[$data['tableName'] . 'Id'];
					$dataArray[$data['dueDate']]['name'] = $data['name'];
					$dataArray[$data['dueDate']]['status'] = $data['statusId'];
					$dataArray[$data['dueDate']]['dueDate'] = $data['dueDate'];
					$dataArray[$data['dueDate']]['delegate'] = ($data['delegateTo']) ? $data['delegateTo'] : $val;
					$dataArray[$data['dueDate']]['showOnDashboard'] = $data['showOnDashboard'];
					$dataArray[$data['dueDate']]['completed'] = $data['completed'];

					$dataArray[$data['dueDate']]['assignDate'] = $data['assignDate'];
					$dataArray[$data['dueDate']]['dueDate'] = $data['dueDate'];
					$dataArray[$data['dueDate']]['goal'] = $data['goal'];
					$dataArray[$data['dueDate']]['accumulate'] = $data['accumulate'];
					$dataArray[$data['dueDate']]['trackSign'] = $data['trackSign'];
					$dataArray[$data['dueDate']]['seasonalGoal'] = $data['seasonalGoal'];
					$dataArray[$data['dueDate']]['includeInAvatar'] = $data['includeInAvatar'];
					$dataArray[$data['dueDate']]['includeInProfile'] = $data['includeInProfile'];

					$arrays[] = $dataArray;
				}
			}
			return count($arrays) ? $arrays : false;
		}
	}


}
