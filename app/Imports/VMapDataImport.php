<?php

namespace App\Imports;

use App\Models\Succession\CriticalActivity;
use App\Models\Succession\Kpi;
use App\Models\Succession\Project;
use App\Models\Succession\Strategy;
use App\Models\Succession\Value;
use App\Models\User;
use App\Services\VMapHelperServices\VMapHelpers;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class VMapDataImport implements ToCollection, WithStartRow
{

    private $vmapId;

    private $helper;
    public function __construct($vmapId)
    {
        $this->vmapId = $vmapId;
        $this->helper = new VMapHelpers();
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collections)
    {
        $values = [];
        $kpis = [];
        $strategy = [];
        $projects = [];
        $criticalActivities = [];

        $data = [];
        $saveValueIds = [];

        $duplicateData = [];

        $collections = $collections->toArray();

        foreach ($collections as $key => $collection) {

            if (!array_key_exists($collection[0], $values)) {
                $values[$collection[0]]['values'] = [];
                $values[$collection[0]]['kpis'] = [];
            }

            if (!is_null($collection[0])) {
                $values[$collection[0]]['values']['vMapId'] = $this->vmapId;
                $values[$collection[0]]['values']['valueTitle'] = $collection[1];
                $values[$collection[0]]['values']['completedDate'] = $collection[2];
            }

            if (isset($collection[3])) {

                $values[$collection[0]]['values']['kpis'][$collection[3]] = [
                    'kpiName' => $collection[4],
                    'delegateTo' => $collection[5],
                    'assignDate' => $collection[6],
                    'dueDate' => $collection[7],
                    'completedDate' => $collection[8] ?? Carbon::now()->format('Y-m-d'),
                    'statusId' => $this->formatStatus($collection[9]),
                    'showOnDashboard' => ($collection[10] == 'Y') ? 0 : 1,
                    'includeInReporting' => ($collection[11] == 'Y') ? 0 : 1,
                    'includeInAvatar' => ($collection[12] == 'Y') ? 0 : 1,
                    'trackSign' => $collection[14] ?? "#",
                    'goal' => $collection[15] ?? 0,
                    'isAccumulate' => ($collection[16] == 'N') ? 0 : 1,
                ];
            }

            if (isset($collection[17])) {
                $values[$collection[0]]['values']['kpis'][$collection[3]]['strategy'][$collection[17]] = [
                    'strategyName' => $collection[18],
                    'delegateTo' => $collection[19],
                    'assignDate' => $collection[20],
                    'dueDate' => $collection[21],
                    'completedDate' => $collection[22] ?? Carbon::now()->format('Y-m-d'),
                    'statusId' => $this->formatStatus($collection[23]),
                    'showOnDashboard' => ($collection[24] == 'Y') ? 0 : 1,
                    'includeInReporting' => ($collection[24] == 'Y') ? 0 : 1,
                    'includeInAvatar' => ($collection[25] == 'Y') ? 0 : 1,
                    'trackSign' => $collection[26] ?? "#",
                    'goal' => $collection[29] ?? 0,
                    'isAccumulate' => ($collection[30] == 'N') ? 0 : 1,
                ];

            }

            if (isset($collection[31]) && $collection[31] !== "") {
                $values[$collection[0]]['values']['kpis'][$collection[3]]['strategy'][$collection[16]]['project'][$collection[31]] = [
                    'projectName' => $collection[32],
                    'delegateTo' => $collection[33],
                    'assignDate' => $collection[34],
                    'dueDate' => $collection[35],
                    'completedDate' => $collection[36] ?? Carbon::now()->format('Y-m-d'),
                    'statusId' => $this->formatStatus($collection[37]),
                    'showOnDashboard' => ($collection[38] == 'Y') ? 0 : 1,
                    'includeInReporting' => ($collection[39] == 'Y') ? 0 : 1,
                    'includeInAvatar' => ($collection[40] == 'Y') ? 0 : 1,
                    'trackSign' => $collection[41] ?? "#",
                    'goal' => $collection[42] ?? 0,
                    'isAccumulate' => ($collection[43] == 'N') ? 0 : 1,
                ];

            }

            if (!is_null($collection[45]) && $collection[45] !== "") {
                $values[$collection[0]]['values']['kpis'][$collection[3]]['strategy'][$collection[16]]['project'][$collection[29]]['criticalActivity'][$collection[42]] = [

                    'criticalActivityName' => $collection[43],
                    'delegateTo' => $collection[44],
                    'assignDate' => $collection[45],
                    'dueDate' => $collection[46],
                    'completedDate' => $collection[47] ?? Carbon::now()->format('Y-m-d'),
                    'statusId' => $this->formatStatus($collection[48]),
                    'showOnDashboard' => ($collection[49] == 'Y') ? 0 : 1,
                    'includeInReporting' => ($collection[50] == 'Y') ? 0 : 1,
                    'includeInAvatar' => ($collection[51] == 'Y') ? 0 : 1,
                    'trackSign' => $collection[52] ?? "#",
                    'goal' => $collection[53] ?? 0,
                    'isAccumulate' => ($collection[54] == 'N') ? 0 : 1,
                ];
            }
        }

        foreach ($values as $valKey => $value) {
            $data[] = $value['values'];

        }

        if (isset($data)) {

            foreach ($data as $key => $valueData) {

                $createValues = Value::create($valueData);
                if (!is_null($valueData) && array_key_exists('kpis', $valueData)) {
                    foreach ($valueData['kpis'] as $kpiKey => $kpiData) {
                        $kpiData['valueId'] = $createValues['valueId'];

                        $createKips = Kpi::create($kpiData);

                        if (!is_null($kpiData) && array_key_exists('strategy', $kpiData)) {
                            foreach ($kpiData['strategy'] as $strategyKey => $strategyData) {
                                $strategyData['kpiId'] = $createKips['kpiId'];
                                $createStrategies = Strategy::create($strategyData);

                                if (!is_null($strategyData) && array_key_exists('project', $strategyData)) {
                                    foreach ($strategyData['project'] as $projectKey => $projectData) {
                                        $projectData['strategyId'] = $createStrategies['strategyId'];
                                        $createProjects = Project::create($projectData);

                                        if (!is_null($projectData) && array_key_exists('criticalActivity', $projectData)) {
                                            foreach ($projectData['criticalActivity'] as $criticalActivityKey => $criticalActivityData) {
                                                $criticalActivityData['criticalActivityId'] = $createProjects['projectId'];
                                                CriticalActivity::create($criticalActivityData);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }
        return $createValues;

    }

    /**
     * fetch the delegate ids using the delegate name in the csv
     *
     * @param string $name
     * @return array
     */
    public static function getDelegateFromId($idValDel)
    {
        if ($idValDel) {
            $getDelArr = explode(',', $idValDel);
        }

        if ($getDelArr) {

            foreach ($getDelArr as $deleIdValId) {
                $getResult = User::select("*")->where('user_id', $deleIdValId)->first()->toArray();
                $getFinalList[] = $getResult['name'];
            }
        }
        if ($getFinalList) {
            return implode(', ', $getFinalList);
        }

    }

    public function _group_by($array, $key)
    {
        $return = array();
        foreach ($array as $val) {
            $return[$val[$key]][] = $val;
        }
        return $return;
    }

    public function formatStatus($status)
    {
        return ($status == 'Pending') ? 0 : (($status == 'In Progress') ? 1 : (($status == 'Completed') ? 2 : ''));
    }
}
