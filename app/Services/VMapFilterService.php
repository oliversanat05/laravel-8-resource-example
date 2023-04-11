<?php

namespace App\Services;
use Auth;
use Request;
use App\Models\Succession\Kpi;
use App\Models\Succession\VMap;
use App\Models\Succession\Value;
use App\Models\Succession\Project;
use App\Models\Succession\Strategy;
use App\Models\Succession\CriticalActivity;

class VMapFilterService
{

    /**
     *
     * This function will get the vMap Dropdown list
     *
     * @param array
     * @return object
     */
    public static function getFilterVMap($vMap)
    {
        $vMap = VMap::whereUserid(Auth::user()->user_id)
                ->when($vMap == null,
                    function($query){
                        return $query->whereNotNull('vMapId');
                    },
                    function($query) use ($vMap){
                        return $query->where('vMapId', $vMap);
                    })
                ->whereIn('isDelete', [1, -1]);
        return $vMap;
    }

    /**
     *
     * This function will get the level1 list
     *
     * @param array
     * @return object
     */
    public static function getFilters($params)
    {
        $vMap = $params['vmap'];
        $level1 = $params['value'];
        $level2 = $params['kpi'];
        $delegate = $params['delegate'];
        $dial = $params['dial'];
        $avatar = $params['avatar'];
        $track = $params['track'];
        $status = $params['status'];
        $deleted = $params['deleted'];

        $vMap = self::getFilterVMap($vMap)
                    ->with(['values' => function($query) use($level1, $level2, $dial, $avatar, $track, $status, $delegate, $deleted){
                    $query
                        ->when($level1 == null,
                            function($q) use($level1, $level2){
                                return $q->whereNotNull('valueId');
                            },
                            function($q) use($level1){
                                return $q->where('valueId', $level1);
                            })
                        ->when($status == null,
                            function($q) use($status){
                                return $q->whereIn('statusId', config('statistics.vMapStatus'));
                            },
                            function($q) use($status){
                                return $q->whereIn('statusId', $status);
                            })
                            ->when($deleted == null, function($query){
                                return $query->whereNotNull('isDelete');
                            }, function($query) use($deleted){
                                return $query->whereIn('isDelete', $deleted);
                            })
                        ->with(['kpis' => function ($query) use($level2, $dial, $avatar, $track, $status, $delegate) {
                        $query
                            ->where(function ($con) use($dial, $avatar, $track) {
                                $con->orWhere('showOnDashboard', $dial)
                                    ->orWhere('includeInReporting', $track)
                                    ->orWhere('includeInAvatar', $avatar);
                            })
                            ->when($delegate == null,
                                function ($q) use($delegate){
                                    return $q->whereNotNull('delegateTo');
                                },
                                function ($q) use($delegate){
                                    return $q->where('delegateTo', $delegate);
                                })
                            ->when($level2 == null,
                                function($q) use($level2){
                                    return $q->whereNotNull('kpiId');
                                },
                                function($q) use($level2){
                                    return $q->where('kpiId', $level2);
                                })
                            ->when($status == null,
                                function($q) use($status){
                                    return $q->whereIn('statusId', config('statistics.vMapStatus'));
                                },
                                function($q) use($status){
                                    return $q->whereIn('statusId', $status);
                                })
                            ->with(['strategy' => function($query) use($dial, $avatar, $track, $status, $delegate){
                            $query
                                ->where(function ($con) use($dial, $avatar, $track) {
                                    $con->orWhere('showOnDashboard', $dial)
                                        ->orWhere('includeInReporting', $track)
                                        ->orWhere('includeInAvatar', $avatar);
                                })
                                ->when($delegate == null,
                                    function ($q) use($delegate){
                                        return $q->whereNotNull('delegateTo');
                                    },
                                    function ($q) use($delegate){
                                        return $q->where('delegateTo', $delegate);
                                    })
                                ->when($status == null,
                                    function($q) use($status){
                                        return $q->whereIn('statusId', config('statistics.vMapStatus'));
                                    },
                                    function($q) use($status){
                                        return $q->whereIn('statusId', $status);
                                    });
                            }]);
                        }]);
                    }]);
        return $vMap;
    }

    /**
     * for getting the vmap filters
     *
     * @param integer $vmapId
     * @return void
     */
    public static function getVmapValues($vmapId)
    {
        $getValues = Value::where('vMapId', $vmapId)->get(
            ['valueId', 'vMapId', 'displayOrder', 'valueTitle']
        )->toArray();

        $valueData = [];
        foreach ($getValues as $key => $value) {
            $valueData[] = [
                'valueId' => $value['valueId'],
                'vMapId' => $value['vMapId'],
                'displayOrder' => $value['displayOrder'],
                'valueTitle' => $value['valueTitle'],
                'type' => 'level1'
            ];
        }

        return $valueData;
    }

    /**
     * get all the kpis related to the vmap values
     *
     * @param $valueId
     * @return void
     */
    public static function getValueKpis(array $valueIds)
    {
        $getKpis = Kpi::whereIn('valueId', $valueIds)->where('isDelete',0)->get([
            'kpiId', 'valueId', 'kpiName', 'kOrder'
        ])->toArray();

        $kpiData = [];
        foreach($getKpis as $kpiKey => $kpi) {
            $kpiData[] = [
                'kpiId' => $kpi['kpiId'],
                'valueId' => $kpi['valueId'],
                'kpiName' => $kpi['kpiName'],
                'kOrder' => $kpi['kOrder'],
                'type' => 'level2'
            ];
        }
        return $kpiData;
    }


    /**
     * for getting all the level 3 related to level2
     *
     * @param array $kpiId
     * @return void
     */
    public static function getKpiStrategies(array $kpiIds)
    {
        $getStrategies = Strategy::whereIn('kpiId', $kpiIds)->get([
            'strategyId', 'kpiId', 'strategyName', 'sOrder'
        ])->toArray();

        $strategyData = [];

        foreach ($getStrategies as $key => $strategy) {
            $strategyData[] = [
                'strategyId' => $strategy['strategyId'],
                'kpiId' => $strategy['kpiId'],
                'strategyName' => $strategy['strategyName'],
                'sOrder' => $strategy['sOrder'],
                'type' => 'level3'
            ];
        }

        return $strategyData;
    }

    /**
     * for getting all the level 4 related to level 3
     *
     * @param array $strategyId
     * @return void
     */
    public static function getStrategyProjects(array $strategyIds)
    {
        $getProjects = Project::whereIn('strategyId', $strategyIds)->get([
            'projectId', 'strategyId', 'projectName', 'pOrder'
        ])->toArray();

        $projectData = [];
        foreach ($getProjects as $key => $project) {
            $projectData[] = [
                'projectId' => $project['projectId'],
                'strategyId' => $project['strategyId'],
                'projectName' => $project['projectName'],
                'pOrder' => $project['pOrder'],
                'type' => 'level4'
            ];
        }
        return $projectData;
    }

    /**
     * get all the level 5 related to level 4
     *
     * @param array $projectIds
     * @return void
     */
    public function getProjectCriticalActivities(array $projectIds)
    {
        $getCriticalActivities = CriticalActivity::whereIn('projectId', $projectIds)->get([
            'criticalActivityId', 'projectId', 'criticalActivityName', 'cOrder'
        ])->toArray();

        $criticalActivityData = [];
        foreach ($getCriticalActivities as $key => $value) {
            $criticalActivityData[] = [
                'criticalActivityId' => $value['criticalActivityId'],
                'projectId' => $value['projectId'],
                'criticalActivityName' => $value['criticalActivityName'],
                'cOrder' => $value['cOrder'],
                'type' => 'level5'
            ];
        }

        return $criticalActivityData;
    }

    /**
     * for getting the vmap filters
     *
     * @param integer $vmapId
     * @return void
     */
    public static function getAllValues($vmapId)
    {
        $getValues = Value::whereIn('vMapId', $vmapId)->where('isDelete',true)->get(
            ['valueId', 'vMapId', 'displayOrder', 'valueTitle']
        );

        return $getValues;
    }

    /**
     * get kpiId details by ID
     *
     * @param $kpiId
     * @return void
     */
    public static function getKpiDetails(int $kpiId)
    {
        return Kpi::select('kpi.*', 'kpi.kpiName AS title', 'kpi.statusId AS status')->where('kpiId',$kpiId)->where('isDelete', 0)->first();
    }

    /**
     * get strategy details by ID
     *
     * @param $strategyId
     * @return void
     */
    public static function getStrategyDetails(int $strategyId)
    {
        return Strategy::select('strategy.*', 'strategy.strategyName AS title', 'strategy.statusId AS status')->where('strategyId', $strategyId)->where('isDelete', 0)->first();
    }

    /**
     * get project details by ID
     *
     * @param $valueId
     * @return void
     */
    public static function getProjectDetails(int $projectId)
    {
        return Project::select('project.*', 'project.projectName AS title', 'project.statusId AS status')->where('projectId', $projectId)->where('isDelete', 0)->first();
    }

    /**
     * get CriticalActivity details by ID
     *
     * @param $criticalActivityId
     * @return void
     */
    public static function getCriticalActivityDetails(int $criticalActivityId)
    {
        return CriticalActivity::select('criticalActivity.*', 'criticalActivity.criticalActivityName AS title', 'criticalActivity.statusId AS status')->where('criticalActivityId', $criticalActivityId)->where('isDelete', 0)->first();
    }
}
