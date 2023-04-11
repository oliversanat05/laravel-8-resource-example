<?php

namespace App\Services\NotificationService;

use DB;
use Auth;
use Config;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\Profile\Profile;
use App\Models\Succession\VMap;
use App\Models\Tracking\DelegateUser;
use App\Models\Tracking\TrackingData;
use App\Models\Notification\AvatarAlert;
use App\Models\Profile\CommunicationOption;
use App\Services\NotificationService\EmailService;
use App\Services\NotificationService\CheckValidDate;
use App\Services\NotificationService\DialsDataService;
use App\Services\NotificationService\LevelsActivityService;


class NotificationService
{

    use ApiResponse;

    public function __construct()
    {
        $this->levels = new LevelsActivityService();
        $this->date = new CheckValidDate();
        $this->email = new EmailService();
        $this->dials = new DialsDataService();
        $this->communication = new CommunicationOption();
        $this->profile = new Profile();
    }

    public $mail = '';
    public $exclude = [2, -1, 0];
    public $services = [];
    public $dueDateCount = [];
    public $userEmails = [];
    public $userDialActivityCounter = [];
    public $userAllActivityCounter = [];
    public $userTemplateHeader = 0;
    public $userActivityCounter = 0;
    private $dialsAlertController;
    public $old_count = 1;
    public $numOfRows = 3;

    public $delegateName = 0;
    public $notficationLinkCheck = 0;
    public $delegateEmail = '';

    public function handle($getDelegate)
    {
        $this->delegateName = 0;
        if (is_array($getDelegate['param']) && $getDelegate['param'][1] != '' && $getDelegate['param'][1] == 'getLink') {
            $this->notficationLinkCheck = $notficationLinkCheck = 1;
            $this->delegateName = $delegateName = $getDelegate['param'][0];
        } else if (is_array($getDelegate['param']) && $getDelegate['param'][1] != '' && is_numeric($getDelegate['param'][1])) {
            $this->delegateEmail = $delegateEmail = $getDelegate['param'][0];
            $this->delegateName = $delegateName = $getDelegate['param'][1];

        } else {
            $this->delegateEmail = $getDelegate['param'];
        }
        ini_set('memory_limit', '160M');

        $this->serverName = env('FRONTEND_APP_URL');
        // $this->serverName = env('APP_URL');


        $checkUserStatus = DelegateUser::where("userId", $getDelegate['param'][0])->orWhere('userId', $getDelegate['param'][1])->where('parentId', Auth::user()->user_id)->first();


        if(!$checkUserStatus->status) {
            return $this->errorApiResponse(__('core.userInactive'));
        } else {

            $users = $this->getAlertUsers();
            return $users;
        }

    }

    /**
     * This function will send notifications to users
     * @param NA
     * @return object
     */
    public function getAlertUsers()
    {
        $comDataArray = $this->getActivityData();

        // dd($comDataArray);

        if(!$comDataArray) {
            return $this->unprocessableApiResponse(__("Please define a prefered communication"));
        } else {
            $dataArray = array();
            $dataDialArray = array();
            $dataArray = $comDataArray['activity'];
            $dataDialArray = $comDataArray['dial'];
            $dataArray = $this->dials->manageHierarchy($dataArray);
            $messageArray = $this->manageMessages($dataArray, $dataDialArray);

            return $messageArray;
        }
    }

    /**
     * this function will get the users data
     * @param NA
     * @return object
     */
    public function getUserData()
    {
        $userData = [];

        $response = $this->communication->getAlertData($this->delegateName);
        // dd($response, "response");

        if (!$response) {

            return false;

        } else {
            foreach ($response as $rows) {
                $userData[] = $rows['eID'];
            }

            return $userData;
        }
    }

    /**
     * This function will get the vmap data
     * @param NA
     * @return object
     */
    public function getQueries($getAlertDataVal)
    {
        $vMap = VMap::where('userId', Auth::user()->user_id)->where('isDelete', true)->with([
            'values' => function ($query) use ($getAlertDataVal) {
                $query->where('isDelete', true)->with([
                    'kpis' => function ($query) use ($getAlertDataVal) {
                        $query->where('isDelete', false)
                            ->whereIn('statusId', Config::get('statistics.vMapStatus'))->with([
                            'strategy' => function ($query) use ($getAlertDataVal) {

                                $query->where('isDelete', false)
                                    ->whereIn('statusId', Config::get('statistics.vMapStatus'))->with([
                                    'project' => function ($query) use ($getAlertDataVal) {

                                        $query->where('isDelete', false)
                                            ->whereIn('statusId', Config::get('statistics.vMapStatus'))->with([
                                            'criticalActivity' => function ($query) use ($getAlertDataVal) {

                                                $query->where('isDelete',
                                                    false)->whereRaw("FIND_IN_SET($getAlertDataVal[0],delegateTo)")
                                                    ->whereIn('statusId', Config::get('statistics.vMapStatus'));
                                            },
                                        ])->orderBy('pOrder');
                                    },
                                ])->orderBy('sOrder');
                            },
                        ])->orderBy('kOrder');
                    },
                ])->orderBy('displayOrder');
            },
        ])->orderBy('formTitle', 'DESC')->get()->toArray();
        return $vMap;
    }

    /**
     * This function will send notifications to users
     * @param NA
     * @return object
     */
    public function getActivityData()
    {
        $alertData = $this->getUserData();

        // dd($alertData);

        if ($alertData === false) {
            return false;
        } else {

            $alertData = array_unique($alertData);
            $queryData = $this->getQueries($alertData);
            $checkCounter = 0;
            $checkDialCounter = 0;
            $dataArray = array();
            $dataDialArray = array();
            foreach ($queryData as $value) {
                # code...
                if (!empty($value['values'])) {

                    foreach ($value['values'] as $valueValues) {
                        # code...
                        $dataArray[$checkCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                        $dataArray[$checkCounter]['vMapId'] = isset($value['vMapId']) ? $value['vMapId'] : '';
                        $dataArray[$checkCounter]['formDate'] = isset($value['formDate']) ? $value['formDate'] : '';
                        $dataArray[$checkCounter]['visionStatement'] = isset($value['visionStatement']) ? $value['visionStatement'] : '';
                        $dataArray[$checkCounter]['missionStatement'] = isset($value['missionStatement']) ? $value['missionStatement'] : '';

                        $dataArray[$checkCounter]['valueId'] = isset($valueValues['valueId']) ? $valueValues['valueId'] : '';
                        $dataArray[$checkCounter]['valueUrl'] = isset($valueValues['valueUrl']) ? $valueValues['valueUrl'] : '';
                        $dataArray[$checkCounter]['valueStatusId'] = isset($valueValues['statusId']) ? $valueValues['statusId'] : '';
                        $dataArray[$checkCounter]['valueCompleted'] = isset($valueValues['completed']) ? $valueValues['completed'] : '';
                        $dataArray[$checkCounter]['displayOrder'] = isset($valueValues['displayOrder']) ? $valueValues['displayOrder'] : '';
                        $dataArray[$checkCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';

                        if (!empty($valueValues['kpis'])) {
                            foreach ($valueValues['kpis'] as $valueKpi) {
                                # code...
                                // data array for dails
                                $dataDialArray[$checkDialCounter]['tableName'] = 'kpi';
                                $dataDialArray[$checkDialCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                                $dataDialArray[$checkDialCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';
                                $dataDialArray[$checkDialCounter]['kpiId'] = isset($valueKpi['kpiId']) ? $valueKpi['kpiId'] : '';
                                $dataDialArray[$checkDialCounter]['name'] = isset($valueKpi['kpiName']) ? $valueKpi['kpiName'] : '';
                                $dataDialArray[$checkDialCounter]['statusId'] = isset($valueKpi['statusId']) ? $valueKpi['statusId'] : '';
                                $dataDialArray[$checkDialCounter]['dueDate'] = isset($valueKpi['dueDate']) ? $valueKpi['dueDate'] : '';
                                $dataDialArray[$checkDialCounter]['delegateTo'] = isset($valueKpi['delegateTo']) ? $valueKpi['delegateTo'] : '';
                                $dataDialArray[$checkDialCounter]['showOnDashboard'] = isset($valueKpi['showOnDashboard']) ? $valueKpi['showOnDashboard'] : '';
                                $dataDialArray[$checkDialCounter]['completed'] = isset($valueKpi['is_complete']) ? $valueKpi['is_complete'] : '';
                                $dataDialArray[$checkDialCounter]['assignDate'] = isset($valueKpi['assignDate']) ? $valueKpi['assignDate'] : '';
                                $dataDialArray[$checkDialCounter]['goal'] = isset($valueKpi['goal']) ? $valueKpi['goal'] : '';
                                $dataDialArray[$checkDialCounter]['accumulate'] = isset($valueKpi['isAccumulate']) ? $valueKpi['isAccumulate'] : '';
                                $dataDialArray[$checkDialCounter]['trackSign'] = isset($valueKpi['trackSign']) ? $valueKpi['trackSign'] : '';
                                $dataDialArray[$checkDialCounter]['seasonalGoal'] = isset($valueKpi['seasonalGoal']) ? $valueKpi['seasonalGoal'] : '';
                                $dataDialArray[$checkDialCounter]['includeInAvatar'] = isset($valueKpi['includeInAvatar']) ? $valueKpi['includeInAvatar'] : '';
                                $dataDialArray[$checkDialCounter]['includeInProfile'] = isset($valueKpi['includeInProfile']) ? $valueKpi['includeInProfile'] : '';
                                // dial data array end

                                $dataArray[$checkCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                                $dataArray[$checkCounter]['vMapId'] = isset($value['vMapId']) ? $value['vMapId'] : '';
                                $dataArray[$checkCounter]['formDate'] = isset($value['formDate']) ? $value['formDate'] : '';
                                $dataArray[$checkCounter]['visionStatement'] = isset($value['visionStatement']) ? $value['visionStatement'] : '';
                                $dataArray[$checkCounter]['missionStatement'] = isset($value['missionStatement']) ? $value['missionStatement'] : '';

                                $dataArray[$checkCounter]['valueId'] = isset($valueValues['valueId']) ? $valueValues['valueId'] : '';
                                $dataArray[$checkCounter]['valueUrl'] = isset($valueValues['valueUrl']) ? $valueValues['valueUrl'] : '';
                                $dataArray[$checkCounter]['valueStatusId'] = isset($valueValues['statusId']) ? $valueValues['statusId'] : '';
                                $dataArray[$checkCounter]['valueCompleted'] = isset($valueValues['completed']) ? $valueValues['completed'] : '';
                                $dataArray[$checkCounter]['displayOrder'] = isset($valueValues['displayOrder']) ? $valueValues['displayOrder'] : '';
                                $dataArray[$checkCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';

                                $dataArray[$checkCounter]['kpiId'] = isset($valueKpi['kpiId']) ? $valueKpi['kpiId'] : '';
                                $dataArray[$checkCounter]['kpiName'] = isset($valueKpi['kpiName']) ? $valueKpi['kpiName'] : '';
                                $dataArray[$checkCounter]['kStatusId'] = isset($valueKpi['statusId']) ? $valueKpi['statusId'] : '';
                                $dataArray[$checkCounter]['kOrder'] = isset($valueKpi['kOrder']) ? $valueKpi['kOrder'] : '';
                                $dataArray[$checkCounter]['kAssignDate'] = isset($valueKpi['assignDate']) ? $valueKpi['assignDate'] : '';
                                $dataArray[$checkCounter]['kCompletedDate'] = isset($valueKpi['completedDate']) ? $valueKpi['completedDate'] : '';
                                $dataArray[$checkCounter]['kDueDate'] = isset($valueKpi['dueDate']) ? $valueKpi['dueDate'] : '';
                                $dataArray[$checkCounter]['kTracking'] = isset($valueKpi['tracking']) ? $valueKpi['tracking'] : '';
                                $dataArray[$checkCounter]['kDaily'] = isset($valueKpi['daily']) ? $valueKpi['daily'] : '';
                                $dataArray[$checkCounter]['kWeekly'] = isset($valueKpi['weekly']) ? $valueKpi['weekly'] : '';
                                $dataArray[$checkCounter]['kMonthly'] = isset($valueKpi['monthly']) ? $valueKpi['monthly'] : '';
                                $dataArray[$checkCounter]['kQuarterly'] = isset($valueKpi['quarterly']) ? $valueKpi['quarterly'] : '';
                                $dataArray[$checkCounter]['kAnnually'] = isset($valueKpi['annually']) ? $valueKpi['annually'] : '';
                                //$dataArray[$checkCounter]['kDescription']= $valueKpi['description'];
                                $dataArray[$checkCounter]['kCompleted'] = isset($valueKpi['is_complete']) ? $valueKpi['is_complete'] : '';
                                $dataArray[$checkCounter]['kShowOnDashBoard'] = isset($valueKpi['showOnDashboard']) ? $valueKpi['showOnDashboard'] : '';
                                $dataArray[$checkCounter]['kIncludeInReporting'] = isset($valueKpi['includeInReporting']) ? $valueKpi['includeInReporting'] : '';
                                $dataArray[$checkCounter]['kIncludeInAvatar'] = isset($valueKpi['includeInAvatar']) ? $valueKpi['includeInAvatar'] : '';
                                //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                $dataArray[$checkCounter]['kTrackingLabel'] = isset($valueKpi['trackingLabel']) ? $valueKpi['trackingLabel'] : '';
                                $dataArray[$checkCounter]['kReferenceId'] = isset($valueKpi['referenceId']) ? $valueKpi['referenceId'] : '';
                                $dataArray[$checkCounter]['kSuccessScale'] = isset($valueKpi['successScale']) ? $valueKpi['successScale'] : '';
                                $dataArray[$checkCounter]['kTrackSign'] = isset($valueKpi['trackSign']) ? $valueKpi['trackSign'] : '';
                                $dataArray[$checkCounter]['kDelegateTo'] = isset($valueKpi['delegateTo']) ? $valueKpi['delegateTo'] : '';
                                $dataArray[$checkCounter]['kQualifierTo'] = isset($valueKpi['qualifierTo']) ? $valueKpi['qualifierTo'] : '';
                                $dataArray[$checkCounter]['kAccumulate'] = isset($valueKpi['isAccumulate']) ? $valueKpi['isAccumulate'] : '';
                                $dataArray[$checkCounter]['kIncludeInProfile'] = isset($valueKpi['includeInProfile']) ? $valueKpi['includeInProfile'] : '';
                                $checkDialCounter++;
                                //$dataArray[$checkCounter]['kpiTree']= $valueKpi['kpiId'];
                                if (!empty($valueKpi['strategy'])) {
                                    foreach ($valueKpi['strategy'] as $valueStrategy) {
                                        # code...
                                        // data array for dails
                                        $dataDialArray[$checkDialCounter]['tableName'] = 'strategy';
                                        $dataDialArray[$checkDialCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                                        $dataDialArray[$checkDialCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';

                                        $dataDialArray[$checkDialCounter]['strategyId'] = isset($valueStrategy['strategyId']) ? $valueStrategy['strategyId'] : '';
                                        $dataDialArray[$checkDialCounter]['name'] = isset($valueStrategy['strategyName']) ? $valueStrategy['strategyName'] : '';
                                        $dataDialArray[$checkDialCounter]['statusId'] = isset($valueStrategy['statusId']) ? $valueStrategy['statusId'] : '';
                                        $dataDialArray[$checkDialCounter]['dueDate'] = isset($valueStrategy['dueDate']) ? $valueStrategy['dueDate'] : '';
                                        $dataDialArray[$checkDialCounter]['delegateTo'] = isset($valueStrategy['delegateTo']) ? $valueStrategy['delegateTo'] : '';
                                        $dataDialArray[$checkDialCounter]['showOnDashboard'] = isset($valueStrategy['showOnDashboard']) ? $valueStrategy['showOnDashboard'] : '';
                                        $dataDialArray[$checkDialCounter]['completed'] = isset($valueStrategy['is_complete']) ? $valueStrategy['is_complete'] : '';
                                        $dataDialArray[$checkDialCounter]['assignDate'] = isset($valueStrategy['assignDate']) ? $valueStrategy['assignDate'] : '';
                                        $dataDialArray[$checkDialCounter]['goal'] = isset($valueStrategy['Goal']) ? $valueStrategy['Goal'] : '';
                                        $dataDialArray[$checkDialCounter]['accumulate'] = isset($valueStrategy['isAccumulate']) ? $valueStrategy['isAccumulate'] : '';
                                        $dataDialArray[$checkDialCounter]['trackSign'] = isset($valueStrategy['trackSign']) ? $valueStrategy['trackSign'] : '';
                                        $dataDialArray[$checkDialCounter]['seasonalGoal'] = isset($valueStrategy['seasonalGoal']) ? $valueStrategy['seasonalGoal'] : '';
                                        $dataDialArray[$checkDialCounter]['includeInAvatar'] = isset($valueStrategy['includeInAvatar']) ? $valueStrategy['includeInAvatar'] : '';
                                        $dataDialArray[$checkDialCounter]['includeInProfile'] = isset($valueStrategy['includeInProfile']) ? $valueStrategy['includeInProfile'] : '';
                                        // dial data array end

                                        $dataArray[$checkCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                                        $dataArray[$checkCounter]['vMapId'] = isset($value['vMapId']) ? $value['vMapId'] : '';
                                        $dataArray[$checkCounter]['formDate'] = isset($value['formDate']) ? $value['formDate'] : '';
                                        $dataArray[$checkCounter]['visionStatement'] = isset($value['visionStatement']) ? $value['visionStatement'] : '';
                                        $dataArray[$checkCounter]['missionStatement'] = isset($value['missionStatement']) ? $value['missionStatement'] : '';

                                        $dataArray[$checkCounter]['valueId'] = isset($valueValues['valueId']) ? $valueValues['valueId'] : '';
                                        $dataArray[$checkCounter]['valueUrl'] = isset($valueValues['valueUrl']) ? $valueValues['valueUrl'] : '';
                                        $dataArray[$checkCounter]['valueStatusId'] = isset($valueValues['statusId']) ? $valueValues['statusId'] : '';
                                        $dataArray[$checkCounter]['valueCompleted'] = isset($valueValues['completed']) ? $valueValues['completed'] : '';
                                        $dataArray[$checkCounter]['displayOrder'] = isset($valueValues['displayOrder']) ? $valueValues['displayOrder'] : '';
                                        $dataArray[$checkCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';

                                        $dataArray[$checkCounter]['kpiId'] = isset($valueKpi['kpiId']) ? $valueKpi['kpiId'] : '';
                                        $dataArray[$checkCounter]['kpiName'] = isset($valueKpi['kpiName']) ? $valueKpi['kpiName'] : '';
                                        $dataArray[$checkCounter]['kStatusId'] = isset($valueKpi['statusId']) ? $valueKpi['statusId'] : '';
                                        $dataArray[$checkCounter]['kOrder'] = isset($valueKpi['kOrder']) ? $valueKpi['kOrder'] : '';
                                        $dataArray[$checkCounter]['kAssignDate'] = isset($valueKpi['assignDate']) ? $valueKpi['assignDate'] : '';
                                        $dataArray[$checkCounter]['kCompletedDate'] = isset($valueKpi['completedDate']) ? $valueKpi['completedDate'] : '';
                                        $dataArray[$checkCounter]['kDueDate'] = isset($valueKpi['dueDate']) ? $valueKpi['dueDate'] : '';
                                        $dataArray[$checkCounter]['kTracking'] = isset($valueKpi['tracking']) ? $valueKpi['tracking'] : '';
                                        $dataArray[$checkCounter]['kDaily'] = isset($valueKpi['daily']) ? $valueKpi['daily'] : '';
                                        $dataArray[$checkCounter]['kWeekly'] = isset($valueKpi['weekly']) ? $valueKpi['weekly'] : '';
                                        $dataArray[$checkCounter]['kMonthly'] = isset($valueKpi['monthly']) ? $valueKpi['monthly'] : '';
                                        $dataArray[$checkCounter]['kQuarterly'] = isset($valueKpi['quarterly']) ? $valueKpi['quarterly'] : '';
                                        $dataArray[$checkCounter]['kAnnually'] = isset($valueKpi['annually']) ? $valueKpi['annually'] : '';
                                        //$dataArray[$checkCounter]['kDescription']= $valueKpi['description'];
                                        $dataArray[$checkCounter]['kCompleted'] = isset($valueKpi['is_complete']) ? $valueKpi['is_complete'] : '';
                                        $dataArray[$checkCounter]['kShowOnDashBoard'] = isset($valueKpi['showOnDashboard']) ? $valueKpi['showOnDashboard'] : '';
                                        $dataArray[$checkCounter]['kIncludeInReporting'] = isset($valueKpi['includeInReporting']) ? $valueKpi['includeInReporting'] : '';
                                        $dataArray[$checkCounter]['kIncludeInAvatar'] = isset($valueKpi['includeInAvatar']) ? $valueKpi['includeInAvatar'] : '';
                                        //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                        $dataArray[$checkCounter]['kTrackingLabel'] = isset($valueKpi['trackingLabel']) ? $valueKpi['trackingLabel'] : '';
                                        $dataArray[$checkCounter]['kReferenceId'] = isset($valueKpi['referenceId']) ? $valueKpi['referenceId'] : '';
                                        $dataArray[$checkCounter]['kSuccessScale'] = isset($valueKpi['successScale']) ? $valueKpi['successScale'] : '';
                                        $dataArray[$checkCounter]['kTrackSign'] = isset($valueKpi['trackSign']) ? $valueKpi['trackSign'] : '';
                                        $dataArray[$checkCounter]['kDelegateTo'] = isset($valueKpi['delegateTo']) ? $valueKpi['delegateTo'] : '';
                                        $dataArray[$checkCounter]['kQualifierTo'] = isset($valueKpi['qualifierTo']) ? $valueKpi['qualifierTo'] : '';
                                        $dataArray[$checkCounter]['kAccumulate'] = isset($valueKpi['isAccumulate']) ? $valueKpi['isAccumulate'] : '';
                                        $dataArray[$checkCounter]['kIncludeInProfile'] = isset($valueKpi['includeInProfile']) ? $valueKpi['includeInProfile'] : '';

                                        $dataArray[$checkCounter]['strategyId'] = isset($valueStrategy['strategyId']) ? $valueStrategy['strategyId'] : '';
                                        $dataArray[$checkCounter]['strategyName'] = isset($valueStrategy['strategyName']) ? $valueStrategy['strategyName'] : '';
                                        $dataArray[$checkCounter]['sStatusId'] = isset($valueStrategy['statusId']) ? $valueStrategy['statusId'] : '';
                                        $dataArray[$checkCounter]['sOrder'] = isset($valueStrategy['sOrder']) ? $valueStrategy['sOrder'] : '';
                                        $dataArray[$checkCounter]['sAssignDate'] = isset($valueStrategy['assignDate']) ? $valueStrategy['assignDate'] : '';
                                        $dataArray[$checkCounter]['sCompletedDate'] = isset($valueStrategy['completedDate']) ? $valueStrategy['completedDate'] : '';
                                        $dataArray[$checkCounter]['sDueDate'] = isset($valueStrategy['dueDate']) ? $valueStrategy['dueDate'] : '';
                                        $dataArray[$checkCounter]['sTracking'] = isset($valueStrategy['tracking']) ? $valueStrategy['tracking'] : '';
                                        $dataArray[$checkCounter]['sDaily'] = isset($valueStrategy['daily']) ? $valueStrategy['daily'] : '';
                                        $dataArray[$checkCounter]['sWeekly'] = isset($valueStrategy['weekly']) ? $valueStrategy['weekly'] : '';
                                        $dataArray[$checkCounter]['sMonthly'] = isset($valueStrategy['monthly']) ? $valueStrategy['monthly'] : '';
                                        $dataArray[$checkCounter]['sQuarterly'] = isset($valueStrategy['quarterly']) ? $valueStrategy['quarterly'] : '';
                                        $dataArray[$checkCounter]['sAnnually'] = isset($valueStrategy['annually']) ? $valueStrategy['annually'] : '';
                                        //$dataArray[$checkCounter]['sDescription']= $valueStrategy['description'];
                                        $dataArray[$checkCounter]['sCompleted'] = isset($valueStrategy['is_complete']) ? $valueStrategy['is_complete'] : '';
                                        $dataArray[$checkCounter]['sShowOnDashBoard'] = isset($valueStrategy['showOnDashboard']) ? $valueStrategy['showOnDashboard'] : '';
                                        $dataArray[$checkCounter]['sIncludeInReporting'] = isset($valueStrategy['includeInReporting']) ? $valueStrategy['includeInReporting'] : '';

                                        $dataArray[$checkCounter]['sIncludeInAvatar'] = isset($valueStrategy['includeInAvatar']) ? $valueStrategy['includeInAvatar'] : '';
                                        $dataArray[$checkCounter]['sIncludeInProfile'] = isset($valueStrategy['includeInProfile']) ? $valueStrategy['includeInProfile'] : '';
                                        //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                        $dataArray[$checkCounter]['sTrackingLabel'] = isset($valueStrategy['trackingLabel']) ? $valueStrategy['trackingLabel'] : '';
                                        $dataArray[$checkCounter]['sReferenceId'] = isset($valueStrategy['referenceId']) ? $valueStrategy['referenceId'] : '';
                                        $dataArray[$checkCounter]['sSuccessScale'] = isset($valueStrategy['successScale']) ? $valueStrategy['successScale'] : '';
                                        $dataArray[$checkCounter]['sTrackSign'] = isset($valueStrategy['trackSign']) ? $valueStrategy['trackSign'] : '';
                                        $dataArray[$checkCounter]['sDelegateTo'] = isset($valueStrategy['delegateTo']) ? $valueStrategy['delegateTo'] : '';
                                        $dataArray[$checkCounter]['sQualifierTo'] = isset($valueStrategy['qualifierTo']) ? $valueStrategy['qualifierTo'] : '';
                                        $dataArray[$checkCounter]['sAccumulate'] = isset($valueStrategy['isAccumulate']) ? $valueStrategy['isAccumulate'] : '';
                                        $checkDialCounter++; //$dataArray[$checkCounter]['kpiTree']= $valueKpi['kpiId'];

                                        if (!empty($valueStrategy['project'])) {
                                            foreach ($valueStrategy['project'] as $valueProject) {
                                                # code...
                                                // data array for dails
                                                $dataDialArray[$checkDialCounter]['tableName'] = 'project';
                                                $dataDialArray[$checkDialCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                                                $dataDialArray[$checkDialCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';

                                                $dataDialArray[$checkDialCounter]['projectId'] = isset($valueProject['projectId']) ? $valueProject['projectId'] : '';
                                                $dataDialArray[$checkDialCounter]['name'] = isset($valueProject['projectName']) ? $valueProject['projectName'] : '';
                                                $dataDialArray[$checkDialCounter]['statusId'] = isset($valueProject['statusId']) ? $valueProject['statusId'] : '';
                                                $dataDialArray[$checkDialCounter]['dueDate'] = isset($valueProject['dueDate']) ? $valueProject['dueDate'] : '';
                                                $dataDialArray[$checkDialCounter]['delegateTo'] = isset($valueProject['delegateTo']) ? $valueProject['delegateTo'] : '';
                                                $dataDialArray[$checkDialCounter]['showOnDashboard'] = isset($valueProject['showOnDashboard']) ? $valueProject['showOnDashboard'] : '';
                                                $dataDialArray[$checkDialCounter]['completed'] = isset($valueProject['is_complete']) ? $valueProject['is_complete'] : '';
                                                $dataDialArray[$checkDialCounter]['assignDate'] = isset($valueProject['assignDate']) ? $valueProject['assignDate'] : '';
                                                $dataDialArray[$checkDialCounter]['goal'] = isset($valueProject['Goal']) ? $valueProject['Goal'] : '';
                                                $dataDialArray[$checkDialCounter]['accumulate'] = isset($valueProject['isAccumulate']) ? $valueProject['isAccumulate'] : '';
                                                $dataDialArray[$checkDialCounter]['trackSign'] = isset($valueProject['trackSign']) ? $valueProject['trackSign'] : '';
                                                $dataDialArray[$checkDialCounter]['seasonalGoal'] = isset($valueProject['seasonalGoal']) ? $valueProject['seasonalGoal'] : '';
                                                $dataDialArray[$checkDialCounter]['includeInAvatar'] = isset($valueProject['includeInAvatar']) ? $valueProject['includeInAvatar'] : '';
                                                $dataDialArray[$checkDialCounter]['includeInProfile'] = isset($valueProject['includeInProfile']) ? $valueProject['includeInProfile'] : '';
                                                // dial data array end

                                                $dataArray[$checkCounter]['userId'] = $value['userId'];
                                                $dataArray[$checkCounter]['vMapId'] = $value['vMapId'];
                                                $dataArray[$checkCounter]['formDate'] = $value['formDate'];
                                                $dataArray[$checkCounter]['visionStatement'] = $value['visionStatement'];
                                                $dataArray[$checkCounter]['missionStatement'] = $value['missionStatement'];

                                                $dataArray[$checkCounter]['valueId'] = $valueValues['valueId'];
                                                $dataArray[$checkCounter]['valueUrl'] = $valueValues['valueUrl'];
                                                $dataArray[$checkCounter]['valueStatusId'] = $valueValues['statusId'];
                                                $dataArray[$checkCounter]['valueCompleted'] = $valueValues['completed'];
                                                $dataArray[$checkCounter]['displayOrder'] = $valueValues['displayOrder'];
                                                $dataArray[$checkCounter]['valueTitle'] = $valueValues['valueTitle'];

                                                $dataArray[$checkCounter]['kpiId'] = $valueKpi['kpiId'];
                                                $dataArray[$checkCounter]['kpiName'] = $valueKpi['kpiName'];
                                                $dataArray[$checkCounter]['kStatusId'] = $valueKpi['statusId'];
                                                $dataArray[$checkCounter]['kOrder'] = $valueKpi['kOrder'];
                                                $dataArray[$checkCounter]['kAssignDate'] = $valueKpi['assignDate'];
                                                $dataArray[$checkCounter]['kCompletedDate'] = $valueKpi['completedDate'];
                                                $dataArray[$checkCounter]['kDueDate'] = $valueKpi['dueDate'];
                                                $dataArray[$checkCounter]['kTracking'] = $valueKpi['tracking'];
                                                $dataArray[$checkCounter]['kDaily'] = $valueKpi['daily'];
                                                $dataArray[$checkCounter]['kWeekly'] = $valueKpi['weekly'];
                                                $dataArray[$checkCounter]['kMonthly'] = $valueKpi['monthly'];
                                                $dataArray[$checkCounter]['kQuarterly'] = $valueKpi['quarterly'];
                                                $dataArray[$checkCounter]['kAnnually'] = $valueKpi['annually'];
                                                //$dataArray[$checkCounter]['kDescription']= $valueKpi['description'];
                                                $dataArray[$checkCounter]['kCompleted'] = $valueKpi['is_complete'];
                                                $dataArray[$checkCounter]['kShowOnDashBoard'] = $valueKpi['showOnDashboard'];
                                                $dataArray[$checkCounter]['kIncludeInReporting'] = $valueKpi['includeInReporting'];
                                                $dataArray[$checkCounter]['kIncludeInAvatar'] = $valueKpi['includeInAvatar'];
                                                $dataArray[$checkCounter]['kIncludeInProfile'] = isset($valueKpi['includeInProfile']) ? $valueKpi['includeInProfile'] : '';
                                                //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                                $dataArray[$checkCounter]['kTrackingLabel'] = $valueKpi['trackingLabel'];
                                                $dataArray[$checkCounter]['kReferenceId'] = $valueKpi['referenceId'];
                                                $dataArray[$checkCounter]['kSuccessScale'] = $valueKpi['successScale'];
                                                $dataArray[$checkCounter]['kTrackSign'] = $valueKpi['trackSign'];
                                                $dataArray[$checkCounter]['kDelegateTo'] = $valueKpi['delegateTo'];
                                                $dataArray[$checkCounter]['kQualifierTo'] = $valueKpi['qualifierTo'];
                                                $dataArray[$checkCounter]['kAccumulate'] = $valueKpi['isAccumulate'];

                                                $dataArray[$checkCounter]['strategyId'] = $valueStrategy['strategyId'];
                                                $dataArray[$checkCounter]['strategyName'] = $valueStrategy['strategyName'];
                                                $dataArray[$checkCounter]['sStatusId'] = $valueStrategy['statusId'];
                                                $dataArray[$checkCounter]['sOrder'] = $valueStrategy['sOrder'];
                                                $dataArray[$checkCounter]['sAssignDate'] = $valueStrategy['assignDate'];
                                                $dataArray[$checkCounter]['sCompletedDate'] = $valueStrategy['completedDate'];
                                                $dataArray[$checkCounter]['sDueDate'] = $valueStrategy['dueDate'];
                                                $dataArray[$checkCounter]['sTracking'] = $valueStrategy['tracking'];
                                                $dataArray[$checkCounter]['sDaily'] = $valueStrategy['daily'];
                                                $dataArray[$checkCounter]['sWeekly'] = $valueStrategy['weekly'];
                                                $dataArray[$checkCounter]['sMonthly'] = $valueStrategy['monthly'];
                                                $dataArray[$checkCounter]['sQuarterly'] = $valueStrategy['quarterly'];
                                                $dataArray[$checkCounter]['sAnnually'] = $valueStrategy['annually'];
                                                //$dataArray[$checkCounter]['sDescription']= $valueStrategy['description'];
                                                $dataArray[$checkCounter]['sCompleted'] = $valueStrategy['is_complete'];
                                                $dataArray[$checkCounter]['sShowOnDashBoard'] = $valueStrategy['showOnDashboard'];
                                                $dataArray[$checkCounter]['sIncludeInReporting'] = $valueStrategy['includeInReporting'];
                                                $dataArray[$checkCounter]['sIncludeInAvatar'] = $valueStrategy['includeInAvatar'];
                                                $dataArray[$checkCounter]['sIncludeInProfile'] = isset($valueStrategy['includeInProfile']) ? $valueStrategy['includeInProfile'] : '';
                                                //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                                $dataArray[$checkCounter]['sTrackingLabel'] = $valueStrategy['trackingLabel'];
                                                $dataArray[$checkCounter]['sReferenceId'] = $valueStrategy['referenceId'];
                                                $dataArray[$checkCounter]['sSuccessScale'] = $valueStrategy['successScale'];
                                                $dataArray[$checkCounter]['sTrackSign'] = $valueStrategy['trackSign'];
                                                $dataArray[$checkCounter]['sDelegateTo'] = $valueStrategy['delegateTo'];
                                                $dataArray[$checkCounter]['sQualifierTo'] = $valueStrategy['qualifierTo'];
                                                $dataArray[$checkCounter]['sAccumulate'] = $valueStrategy['isAccumulate'];

                                                $dataArray[$checkCounter]['projectId'] = $valueProject['projectId'];
                                                $dataArray[$checkCounter]['projectName'] = $valueProject['projectName'];
                                                $dataArray[$checkCounter]['pStatusId'] = $valueProject['statusId'];
                                                $dataArray[$checkCounter]['pOrder'] = $valueProject['pOrder'];
                                                $dataArray[$checkCounter]['pAssignDate'] = $valueProject['assignDate'];
                                                $dataArray[$checkCounter]['pCompletedDate'] = $valueProject['completedDate'];
                                                $dataArray[$checkCounter]['pDueDate'] = $valueProject['dueDate'];
                                                $dataArray[$checkCounter]['pTracking'] = $valueProject['tracking'];
                                                $dataArray[$checkCounter]['pDaily'] = $valueProject['daily'];
                                                $dataArray[$checkCounter]['pWeekly'] = $valueProject['weekly'];
                                                $dataArray[$checkCounter]['pMonthly'] = $valueProject['monthly'];
                                                $dataArray[$checkCounter]['pQuarterly'] = $valueProject['quarterly'];
                                                $dataArray[$checkCounter]['pAnnually'] = $valueProject['annually'];
                                                //$dataArray[$checkCounter]['pDescription']= $valueProject['description'];
                                                $dataArray[$checkCounter]['pCompleted'] = $valueProject['is_complete'];
                                                $dataArray[$checkCounter]['pShowOnDashBoard'] = $valueProject['showOnDashboard'];
                                                $dataArray[$checkCounter]['pIncludeInReporting'] = $valueProject['includeInReporting'];
                                                $dataArray[$checkCounter]['pIncludeInAvatar'] = $valueProject['includeInAvatar'];
                                                $dataArray[$checkCounter]['pIncludeInProfile'] = isset($valueProject['includeInProfile']) ? $valueProject['includeInProfile'] : '';
                                                //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                                $dataArray[$checkCounter]['pTrackingLabel'] = $valueProject['trackingLabel'];
                                                $dataArray[$checkCounter]['pReferenceId'] = $valueProject['referenceId'];
                                                $dataArray[$checkCounter]['pSuccessScale'] = $valueProject['successScale'];
                                                $dataArray[$checkCounter]['pTrackSign'] = $valueProject['trackSign'];
                                                $dataArray[$checkCounter]['pDelegateTo'] = $valueProject['delegateTo'];
                                                $dataArray[$checkCounter]['pQualifierTo'] = $valueProject['qualifierTo'];
                                                $dataArray[$checkCounter]['pAccumulate'] = $valueProject['isAccumulate'];
                                                $checkDialCounter++;

                                                if (!empty($valueProject['critical_activity'])) {
                                                    foreach ($valueProject['critical_activity'] as $valueCritical) {
                                                        # code...
                                                        // data array for dails
                                                        $dataDialArray[$checkDialCounter]['tableName'] = 'criticalActivity';
                                                        $dataDialArray[$checkDialCounter]['userId'] = isset($value['userId']) ? $value['userId'] : '';
                                                        $dataDialArray[$checkDialCounter]['valueTitle'] = isset($valueValues['valueTitle']) ? $valueValues['valueTitle'] : '';

                                                        $dataDialArray[$checkDialCounter]['criticalActivityId'] = isset($valueCritical['criticalActivityId']) ? $valueCritical['criticalActivityId'] : '';
                                                        $dataDialArray[$checkDialCounter]['name'] = isset($valueCritical['criticalActivityName']) ? $valueCritical['criticalActivityName'] : '';
                                                        $dataDialArray[$checkDialCounter]['statusId'] = isset($valueCritical['statusId']) ? $valueCritical['statusId'] : '';
                                                        $dataDialArray[$checkDialCounter]['dueDate'] = isset($valueCritical['dueDate']) ? $valueCritical['dueDate'] : '';
                                                        $dataDialArray[$checkDialCounter]['delegateTo'] = isset($valueCritical['delegateTo']) ? $valueCritical['delegateTo'] : '';
                                                        $dataDialArray[$checkDialCounter]['showOnDashboard'] = isset($valueCritical['showOnDashboard']) ? $valueCritical['showOnDashboard'] : '';
                                                        $dataDialArray[$checkDialCounter]['completed'] = isset($valueCritical['is_complete']) ? $valueCritical['is_complete'] : '';
                                                        $dataDialArray[$checkDialCounter]['assignDate'] = isset($valueCritical['assignDate']) ? $valueCritical['assignDate'] : '';
                                                        $dataDialArray[$checkDialCounter]['goal'] = isset($valueCritical['Goal']) ? $valueCritical['Goal'] : '';
                                                        $dataDialArray[$checkDialCounter]['accumulate'] = isset($valueCritical['isAccumulate']) ? $valueCritical['isAccumulate'] : '';
                                                        $dataDialArray[$checkDialCounter]['trackSign'] = isset($valueCritical['trackSign']) ? $valueCritical['trackSign'] : '';
                                                        $dataDialArray[$checkDialCounter]['seasonalGoal'] = isset($valueCritical['seasonalGoal']) ? $valueCritical['seasonalGoal'] : '';
                                                        $dataDialArray[$checkDialCounter]['includeInAvatar'] = isset($valueCritical['includeInAvatar']) ? $valueCritical['includeInAvatar'] : '';
                                                        $dataDialArray[$checkDialCounter]['includeInProfile'] = isset($valueCritical['includeInProfile']) ? $valueCritical['includeInProfile'] : '';

                                                        // dial data array end
                                                        $dataArray[$checkCounter]['userId'] = $value['userId'];
                                                        $dataArray[$checkCounter]['vMapId'] = $value['vMapId'];
                                                        $dataArray[$checkCounter]['formDate'] = $value['formDate'];
                                                        $dataArray[$checkCounter]['visionStatement'] = $value['visionStatement'];
                                                        $dataArray[$checkCounter]['missionStatement'] = $value['missionStatement'];

                                                        $dataArray[$checkCounter]['valueId'] = $valueValues['valueId'];
                                                        $dataArray[$checkCounter]['valueUrl'] = $valueValues['valueUrl'];
                                                        $dataArray[$checkCounter]['valueStatusId'] = $valueValues['statusId'];
                                                        $dataArray[$checkCounter]['valueCompleted'] = $valueValues['completed'];
                                                        $dataArray[$checkCounter]['displayOrder'] = $valueValues['displayOrder'];
                                                        $dataArray[$checkCounter]['valueTitle'] = $valueValues['valueTitle'];

                                                        $dataArray[$checkCounter]['kpiId'] = $valueKpi['kpiId'];
                                                        $dataArray[$checkCounter]['kpiName'] = $valueKpi['kpiName'];
                                                        $dataArray[$checkCounter]['kStatusId'] = $valueKpi['statusId'];
                                                        $dataArray[$checkCounter]['kOrder'] = $valueKpi['kOrder'];
                                                        $dataArray[$checkCounter]['kAssignDate'] = $valueKpi['assignDate'];
                                                        $dataArray[$checkCounter]['kCompletedDate'] = $valueKpi['completedDate'];
                                                        $dataArray[$checkCounter]['kDueDate'] = $valueKpi['dueDate'];
                                                        $dataArray[$checkCounter]['kTracking'] = $valueKpi['tracking'];
                                                        $dataArray[$checkCounter]['kDaily'] = $valueKpi['daily'];
                                                        $dataArray[$checkCounter]['kWeekly'] = $valueKpi['weekly'];
                                                        $dataArray[$checkCounter]['kMonthly'] = $valueKpi['monthly'];
                                                        $dataArray[$checkCounter]['kQuarterly'] = $valueKpi['quarterly'];
                                                        $dataArray[$checkCounter]['kAnnually'] = $valueKpi['annually'];
                                                        //$dataArray[$checkCounter]['kDescription']= $valueKpi['description'];
                                                        $dataArray[$checkCounter]['kCompleted'] = $valueKpi['is_complete'];
                                                        $dataArray[$checkCounter]['kShowOnDashBoard'] = $valueKpi['showOnDashboard'];
                                                        $dataArray[$checkCounter]['kIncludeInReporting'] = $valueKpi['includeInReporting'];
                                                        $dataArray[$checkCounter]['kIncludeInAvatar'] = $valueKpi['includeInAvatar'];
                                                        $dataArray[$checkCounter]['kIncludeInProfile'] = isset($valueKpi['includeInProfile']) ? $valueKpi['includeInProfile'] : '';
                                                        //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                                        $dataArray[$checkCounter]['kTrackingLabel'] = $valueKpi['trackingLabel'];
                                                        $dataArray[$checkCounter]['kReferenceId'] = $valueKpi['referenceId'];
                                                        $dataArray[$checkCounter]['kSuccessScale'] = $valueKpi['successScale'];
                                                        $dataArray[$checkCounter]['kTrackSign'] = $valueKpi['trackSign'];
                                                        $dataArray[$checkCounter]['kDelegateTo'] = $valueKpi['delegateTo'];
                                                        $dataArray[$checkCounter]['kQualifierTo'] = $valueKpi['qualifierTo'];
                                                        $dataArray[$checkCounter]['kAccumulate'] = $valueKpi['isAccumulate'];

                                                        $dataArray[$checkCounter]['strategyId'] = $valueStrategy['strategyId'];
                                                        $dataArray[$checkCounter]['strategyName'] = $valueStrategy['strategyName'];
                                                        $dataArray[$checkCounter]['sStatusId'] = $valueStrategy['statusId'];
                                                        $dataArray[$checkCounter]['sOrder'] = $valueStrategy['sOrder'];
                                                        $dataArray[$checkCounter]['sAssignDate'] = $valueStrategy['assignDate'];
                                                        $dataArray[$checkCounter]['sCompletedDate'] = $valueStrategy['completedDate'];
                                                        $dataArray[$checkCounter]['sDueDate'] = $valueStrategy['dueDate'];
                                                        $dataArray[$checkCounter]['sTracking'] = $valueStrategy['tracking'];
                                                        $dataArray[$checkCounter]['sDaily'] = $valueStrategy['daily'];
                                                        $dataArray[$checkCounter]['sWeekly'] = $valueStrategy['weekly'];
                                                        $dataArray[$checkCounter]['sMonthly'] = $valueStrategy['monthly'];
                                                        $dataArray[$checkCounter]['sQuarterly'] = $valueStrategy['quarterly'];
                                                        $dataArray[$checkCounter]['sAnnually'] = $valueStrategy['annually'];
                                                        //$dataArray[$checkCounter]['sDescription']= $valueStrategy['description'];
                                                        $dataArray[$checkCounter]['sCompleted'] = $valueStrategy['is_complete'];
                                                        $dataArray[$checkCounter]['sShowOnDashBoard'] = $valueStrategy['showOnDashboard'];
                                                        $dataArray[$checkCounter]['sIncludeInReporting'] = $valueStrategy['includeInReporting'];
                                                        $dataArray[$checkCounter]['sIncludeInAvatar'] = $valueStrategy['includeInAvatar'];
                                                        $dataArray[$checkCounter]['sIncludeInProfile'] = isset($valueStrategy['includeInProfile']) ? $valueStrategy['includeInProfile'] : '';
                                                        //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                                        $dataArray[$checkCounter]['sTrackingLabel'] = $valueStrategy['trackingLabel'];
                                                        $dataArray[$checkCounter]['sReferenceId'] = $valueStrategy['referenceId'];
                                                        $dataArray[$checkCounter]['sSuccessScale'] = $valueStrategy['successScale'];
                                                        $dataArray[$checkCounter]['sTrackSign'] = $valueStrategy['trackSign'];
                                                        $dataArray[$checkCounter]['sDelegateTo'] = $valueStrategy['delegateTo'];
                                                        $dataArray[$checkCounter]['sQualifierTo'] = $valueStrategy['qualifierTo'];
                                                        $dataArray[$checkCounter]['sAccumulate'] = $valueStrategy['isAccumulate'];

                                                        $dataArray[$checkCounter]['projectId'] = $valueProject['projectId'];
                                                        $dataArray[$checkCounter]['projectName'] = $valueProject['projectName'];
                                                        $dataArray[$checkCounter]['pStatusId'] = $valueProject['statusId'];
                                                        $dataArray[$checkCounter]['pOrder'] = $valueProject['pOrder'];
                                                        $dataArray[$checkCounter]['pAssignDate'] = $valueProject['assignDate'];
                                                        $dataArray[$checkCounter]['pCompletedDate'] = $valueProject['completedDate'];
                                                        $dataArray[$checkCounter]['pDueDate'] = $valueProject['dueDate'];
                                                        $dataArray[$checkCounter]['pTracking'] = $valueProject['tracking'];
                                                        $dataArray[$checkCounter]['pDaily'] = $valueProject['daily'];
                                                        $dataArray[$checkCounter]['pWeekly'] = $valueProject['weekly'];
                                                        $dataArray[$checkCounter]['pMonthly'] = $valueProject['monthly'];
                                                        $dataArray[$checkCounter]['pQuarterly'] = $valueProject['quarterly'];
                                                        $dataArray[$checkCounter]['pAnnually'] = $valueProject['annually'];

                                                        $dataArray[$checkCounter]['pCompleted'] = $valueProject['is_complete'];
                                                        $dataArray[$checkCounter]['pShowOnDashBoard'] = $valueProject['showOnDashboard'];
                                                        $dataArray[$checkCounter]['pIncludeInReporting'] = $valueProject['includeInReporting'];
                                                        $dataArray[$checkCounter]['pIncludeInAvatar'] = $valueProject['includeInAvatar'];
                                                        $dataArray[$checkCounter]['pIncludeInProfile'] = isset($valueProject['includeInProfile']) ? $valueProject['includeInProfile'] : '';

                                                        $dataArray[$checkCounter]['pTrackingLabel'] = $valueProject['trackingLabel'];
                                                        $dataArray[$checkCounter]['pReferenceId'] = $valueProject['referenceId'];
                                                        $dataArray[$checkCounter]['pSuccessScale'] = $valueProject['successScale'];
                                                        $dataArray[$checkCounter]['pTrackSign'] = $valueProject['trackSign'];
                                                        $dataArray[$checkCounter]['pDelegateTo'] = $valueProject['delegateTo'];
                                                        $dataArray[$checkCounter]['pQualifierTo'] = $valueProject['qualifierTo'];
                                                        $dataArray[$checkCounter]['pAccumulate'] = $valueProject['isAccumulate'];

                                                        $dataArray[$checkCounter]['criticalActivityId'] = $valueCritical['criticalActivityId'];
                                                        $dataArray[$checkCounter]['criticalActivityName'] = $valueCritical['criticalActivityName'];
                                                        $dataArray[$checkCounter]['cStatusId'] = $valueCritical['statusId'];
                                                        $dataArray[$checkCounter]['cOrder'] = $valueCritical['cOrder'];
                                                        $dataArray[$checkCounter]['cAssignDate'] = $valueCritical['assignDate'];
                                                        $dataArray[$checkCounter]['cCompletedDate'] = $valueCritical['completedDate'];
                                                        $dataArray[$checkCounter]['cDueDate'] = $valueCritical['dueDate'];
                                                        $dataArray[$checkCounter]['cTracking'] = $valueCritical['tracking'];
                                                        $dataArray[$checkCounter]['cDaily'] = $valueCritical['daily'];
                                                        $dataArray[$checkCounter]['cWeekly'] = $valueCritical['weekly'];
                                                        $dataArray[$checkCounter]['cMonthly'] = $valueCritical['monthly'];
                                                        $dataArray[$checkCounter]['cQuarterly'] = $valueCritical['quarterly'];
                                                        $dataArray[$checkCounter]['cAnnually'] = $valueCritical['annually'];
                                                        //$dataArray[$checkCounter]['cDescription']= $valueCritical['description'];
                                                        $dataArray[$checkCounter]['cCompleted'] = $valueCritical['is_complete'];
                                                        $dataArray[$checkCounter]['cShowOnDashBoard'] = $valueCritical['showOnDashboard'];
                                                        $dataArray[$checkCounter]['cIncludeInReporting'] = $valueProject['includeInReporting'];
                                                        $dataArray[$checkCounter]['cIncludeInAvatar'] = $valueCritical['includeInAvatar'];
                                                        $dataArray[$checkCounter]['cIncludeInProfile'] = isset($valueCritical['includeInProfile']) ? $valueCritical['includeInProfile'] : '';
                                                        //$dataArray[$checkCounter]['kCheckIfCurrency']= $valueKpi['kpiId'];
                                                        $dataArray[$checkCounter]['cTrackingLabel'] = $valueCritical['trackingLabel'];
                                                        $dataArray[$checkCounter]['cReferenceId'] = $valueCritical['referenceId'];
                                                        $dataArray[$checkCounter]['cSuccessScale'] = $valueCritical['successScale'];
                                                        $dataArray[$checkCounter]['cTrackSign'] = $valueCritical['trackSign'];
                                                        $dataArray[$checkCounter]['cDelegateTo'] = $valueCritical['delegateTo'];
                                                        $dataArray[$checkCounter]['cQualifierTo'] = $valueCritical['qualifierTo'];
                                                        $dataArray[$checkCounter]['cAccumulate'] = $valueCritical['isAccumulate'];

                                                        $checkCounter++;
                                                        $checkDialCounter++;

                                                    }
                                                }
                                                $checkCounter++;
                                            }
                                        }
                                        $checkCounter++;
                                    }
                                }
                                $checkCounter++;
                            }
                        }
                        $checkCounter++;
                    }

                }
            }
            $dataArrayVal = array();
            $dataDialValArrayVal = array();
            $finalDailAcitivityArr = array();

            if (!empty($dataDialArray)) {
                $dataDialValArrayVal = $this->getDialsData($dataDialArray, $alertData);
            }
            foreach ($dataArray as $rows) {
                $dataArrayVal[] = $this->performAction($rows, $alertData);
            }
            $dataArrayVal = array_filter($dataArrayVal);
            $finalDailAcitivityArr['activity'] = $dataArrayVal;
            $finalDailAcitivityArr['dial'] = $dataDialValArrayVal;

            return $finalDailAcitivityArr;
        }

    }

    /**
     * this function will return the level2, level3, level4 and level5 activity data
     * @param $rows, $alertData
     * @return count
     */
    public function performAction($rows, $alertData)
    {

        $dataArray = array();
        $dataArray[] = $this->levels->getLevel2Activty($rows, $alertData);
        $dataArray[] = $this->levels->getLevel3Activty($rows, $alertData);
        $dataArray[] = $this->levels->getLevel4Activty($rows, $alertData);
        $dataArray[] = $this->levels->getLevel5Activty($rows, $alertData);

        $dataArray = array_filter($dataArray);
        return count($dataArray) ? $dataArray : false;
    }

    /**
     * this function will get the dials data
     * @param $getDialsDataVal, $alertDataVal
     * @return array
     */
    public function getDialsData($getDialsDataVal, $alertDataVal)
    {
        $dataDailArray = array();

        foreach ($getDialsDataVal as $dialValue) {
            # code...
            $dataDailArray[] = $this->performDialAction($dialValue, $alertDataVal);
        }

        $dataDailArray = array_filter($dataDailArray);
        $dataDailArray = $this->dials->manageHierarchy($dataDailArray);
        $dataDailArray = $this->manageDialsMessages($dataDailArray);

        return $dataDailArray;
    }

    /**
     * this function will manage the dials messages
     * @param $dataArray
     * @return array
     */
    public function manageDialsMessages($dataArray)
    {

        $messageArray = array();

        $userData = array();
        $setting = array();

        $setting = $this->email->getCommunicationOption();
        if (count($dataArray)) {

            foreach ($dataArray as $keys => $values) {

                foreach ($values as $key => $val) {

                    $array = $this->getPreferredOptions($val['userId'], $val['delegate']);

                    foreach ($array as $index => $data) {

                        $type = 'All';

                        $preferedDate = $this->date->getDateConvert($type, $data);

                        if ($this->date->is_valid($type, $preferedDate, $val['dueDate'], $data['startDate'], $data['endDate'])) {

                            if (!in_array($val['status'], $this->exclude) && $val['showOnDashboard'] == 1 && $val['completed'] != 1) {
                                $messageArray[$data['eID']][$val['dueDate']][$val['id']] = $val;
                                $key_array = explode(',', $val['delegate']);

                                foreach ($key_array as $index => $arx) {

                                    $outData = $this->getPreferredOptions($val['userId'], $arx);
                                    if ($this->date->is_valid($type, $preferedDate, $val['dueDate'], $data['startDate'], $data['endDate'])) {
                                        $userData[$val['userId'] . '-' . $arx] = $outData;
                                    }

                                }

                            }

                        }
                    }
                }
            }
        }
        return $this->createDialsMessages($messageArray, $setting, $userData);
    }

    /**
     * this function will create the dials messages
     * @param $userData, $messageArray, $setting
     * @return array
     */
    public function createDialsMessages($messageArray, $setting, $userData)
    {

        $dataGet = array();
        $dataContent = array();
        foreach ($messageArray as $key => $val) {

            $masterkey = md5(uniqid(rand(), true)) . strtotime(date('Y-m-d H:i:s'));

            $dataGet = $this->addDialsMessages($key, $val, $masterkey, $setting, $userData);

            if (count($dataGet)) {

                foreach ($dataGet as $index => $array) {

                    foreach ($array as $ind => $value) {

                        if ($ind == 'keys') {
                            $dataContent['dataContent'][$array['keys']][$ind] = $value;
                        } else {
                            $dataContent['dataContent'][$array['keys']][$ind][] = $value;
                        }

                    }
                }
            }
            $dataContent[$key]['masterkey'] = $masterkey;
            $dataContent['users'] = $userData;
        }
        return $dataContent;
    }

    /**
     * this function will add the dials messages to the dial
     * @param $userId, $masterkey, $dataArray, $setting, $data
     * @return array
     */
    public function addDialsMessages($userId, $dataArray, $masterkey, $setting, $data)
    {

        $urldecode = $this->serverName;

        $delegate = array();
        $dataContent = array();
        $dataReturn = array();
        $result = [];
        $alertData = $this->dials->getDelegateData($this->delegateName);
        foreach ($dataArray as $keys => $values) {

            if (count($values)) {

                foreach ($values as $key => $val) {
                    if (in_array($val['userId'], $alertData)) {

                        $key_array = explode(',', $userId);

                        foreach ($key_array as $index => $arx) {

                            if ($this->email->is_sender_valid($setting, $data, $val['userId'], $arx)) {
                                $key = md5(uniqid(rand(), true)) . strtotime(date('Y-m-d H:i:s'));
                                $getDateRangeVal = $this->profile->getUserSetDateRange($val['userId']);
                                $filterActiCheck = false;
                                $getDateRangeVal['goal'] = $val['goal'];
                                $getDateRangeVal['trackSign'] = trim($val['trackSign']);
                                $getDateRangeVal['seasonalGoal'] = $val['seasonalGoal'];
                                $getDateRangeVal['assignDate'] = $val['assignDate'];
                                $getDateRangeVal['dueDate'] = $val['dueDate'];
                                $getDateRangeVal['actualAssignDate'] = $val['assignDate'];
                                $getDateRangeVal['actualDueDate'] = $val['dueDate'];
                                $getDateRangeVal['accumulate'] = $val['accumulate'];

                                if ($getDateRangeVal['rangeStartDate'] > $getDateRangeVal['dueDate'] || $getDateRangeVal['rangeEndDate'] < $getDateRangeVal['assignDate']) {
                                    $filterActiCheck = true;
                                }
                                if ($getDateRangeVal['assignDate'] < $getDateRangeVal['rangeStartDate']) {
                                    $getDateRangeVal['assignDate'] = $getDateRangeVal['rangeStartDate'];
                                }

                                if ($getDateRangeVal['dueDate'] > $getDateRangeVal['rangeEndDate']) {
                                    $getDateRangeVal['dueDate'] = $getDateRangeVal['rangeEndDate'];
                                }

                                $activityGet = array();
                                if ($val['type'] == 'value') {
                                    $activityGet['level1'] = $val['id'];
                                }

                                if ($val['type'] == 'kpi') {
                                    $activityGet['level2'] = $val['id'];
                                }

                                if ($val['type'] == 'strategy') {
                                    $activityGet['level3'] = $val['id'];
                                }

                                if ($val['type'] == 'project') {
                                    $activityGet['level4'] = $val['id'];
                                }

                                if ($val['type'] == 'criticalActivity') {
                                    $activityGet['level5'] = $val['id'];
                                }

                                if ($filterActiCheck) {
                                    $getActData = 0;
                                } else {
                                    $getActData = $this->getTrackingItems($val['id'], $activityGet, $getDateRangeVal);
                                }

                                $is_accumulate = (trim($val['accumulate']) == 1) ? 'accumActual' : 'totActual';

                                $totActual = $this->setTrackingData($getActData, $is_accumulate, $val['id']);

                                $tDate = $this->setTrackingData($getActData, 'tDate', $val['id']);
                                $getDateRangeVal['dueDate'] = $this->setEndDateUsingTrack($tDate, $getDateRangeVal);
                                $label = (trim($val['accumulate']) == 1) ? ' Run Rate' : ' Run Rate';

                                if ($filterActiCheck) {
                                    $goalVal = 0;
                                } else {
                                    $goalVal = $this->dials->getGoalValueUsingDates($getDateRangeVal);
                                }

                                $val['curentGoal'] = $goalVal;

                                // If goal type seasonal
                                if ($val['trackSign'] == '@') {
                                    $seasonal = 0;
                                    $seasTs1 = strtotime($getDateRangeVal['actualAssignDate']);
                                    $seasTs2 = strtotime($getDateRangeVal['actualDueDate']);

                                    $seasMonth1 = date('m', $seasTs1);
                                    $seasMonth2 = date('m', $seasTs2);
                                    $val['goal'] = explode(',', $val['seasonalGoal']);
                                    if ($val['goal']) {
                                        foreach ($val['goal'] as $seasKey => $seasValue) {
                                            # code...
                                            // check seasonal goal value from start date to end date
                                            if ($seasKey >= $seasMonth1 - 1 && $seasKey <= $seasMonth2 - 1) {
                                                if (isset($seasValue) && $seasValue != '') {
                                                    if ($getDateRangeVal['accumulate'] == 1) {
                                                        $seasonal = isset($seasValue) ? trim($seasValue) : 0;
                                                    } else {
                                                        $seasonal += isset($seasValue) ? trim($seasValue) : 0;
                                                    }

                                                }
                                            }
                                        }
                                    }
                                    $val['goal'] = $seasonal;
                                }

                                $runRateCal = $this->getRunRateCal($totActual, $goalVal, $getDateRangeVal, 0);

                                $runrate = (trim($val['accumulate']) == 1) ? ($this->getRunrateForAccumulate($getActData, $getDateRangeVal, $totActual, 0)) : $runRateCal;

                                $movment = $this->setTrackingData($getActData, 'accumulate', $val['id']);

                                $color = (trim($val['accumulate']) == 1 && $movment >= 0) ? '#0C0' : '#c00';

                                $result['url'] = $urldecode . 'group-update/key/' . $key;
                                $result['name'] = $val['name'];
                                $result['goal'] = number_format(round($val['goal']), 0, '.', ',');
                                $result['totalActual'] = number_format(round($totActual), 0, '.', ',');
                                $result['currentGoal'] = number_format($val['curentGoal'], 0, '.', ',');
                                $result['label'] = $label;
                                $result['runRate'] = round($runrate, 2);
                                $result['assignDate'] = $val['assignDate'];
                                $result['trackingDate'] = (($tDate) ? date("Y-m-d", strtotime($tDate)) : '');
                                $result['dueDate'] = $val['dueDate'];

                                $dataContent[] = $result;

                                $this->userTemplateHeader++;

                                $result = [];
                                $this->old_count++;
                                // commented just for testing purpose will uncomment after testing
                                $this->getDialsInsertIntoDB($val, $masterkey, $key);

                                //Store delegate and its activity count and percentage datails for email performance top header.
                                if (!in_array($val['status'], $this->exclude) && $val['includeInProfile'] == 1 && $val['showOnDashboard'] == 1 && $val['completed'] != 1) {

                                    $runRateCalForTop = $this->getRunRateCal($totActual, $goalVal, $getDateRangeVal, 1);

                                    $runrateForTop = (trim($val['accumulate']) == 1) ? ($this->getRunrateForAccumulate($getActData, $getDateRangeVal, $totActual, 1)) : $runRateCalForTop;

                                    $val['finalRunrate'] = $runrateForTop;
                                    if (!isset($this->userDialActivityCounter['DialCounter'][$val['userId'] . '-' . $arx])) {
                                        $this->userDialActivityCounter['DialCounter'][$val['userId'] . '-' . $arx] = 1;
                                        $this->userDialActivityCounter['DialCounter']['activity'][$val['userId'] . '-' . $arx][] = $val;
                                    } else {
                                        $this->userDialActivityCounter['DialCounter'][$val['userId'] . '-' . $arx] = $this->userDialActivityCounter['DialCounter'][$val['userId'] . '-' . $arx] + 1;
                                        $this->userDialActivityCounter['DialCounter']['activity'][$val['userId'] . '-' . $arx][] = $val;
                                    }

                                }
                            }

                        }

                    }
                }
            }
        }

        $incr = 0;
        foreach ($dataContent as $keys => $values) {

            $dataReturn[$incr]['keys'] = $keys;
            $dataReturn[$incr]['values'] = $values;
            $incr++;
        }

        return $dataReturn;
    }

    /**
     * this function will get the tracking data
     * @param $getActId, $trackingDataIds, $getDateRanges
     * @return array
     */
    public function getTrackingItems($getActId, $trackingDataIds, $getDateRanges)
    {

        $response = [];
        $startDate = isset($getDateRanges['assignDate']) ? $getDateRanges['assignDate'] : date("Y-01-01");
        $endDate = isset($getDateRanges['dueDate']) ? $getDateRanges['dueDate'] : date("Y-12-31");

        if ($getDateRanges['assignDate'] == 'NULL' || $getDateRanges['assignDate'] == '') {
            $startDate = date("Y-01-01");
        }

        if ($getDateRanges['dueDate'] == 'NULL' || $getDateRanges['dueDate'] == '') {
            $endDate = date("Y-12-31");
        }

        if (isset($trackingDataIds['level2']) || isset($trackingDataIds['level3']) || isset($trackingDataIds['level4']) || isset($trackingDataIds['level5'])):

            $response = TrackingData::select('trackingDate', 'trackingValue', 'kpiId', 'strategyId', 'projectId', 'criticalActivityId', DB::raw('DATE_FORMAT(endDate, "%m/%d/%Y") as endDate'))
                ->where(function ($query) use ($trackingDataIds) {
                    if (isset($trackingDataIds['level2'])) {
                        $query->where('kpiId', $trackingDataIds['level2']);
                    }

                    if (isset($trackingDataIds['level3'])) {
                        $query->where('strategyId', $trackingDataIds['level3']);
                    }

                    if (isset($trackingDataIds['level4'])) {
                        $query->where('projectId', $trackingDataIds['level4']);
                    }

                    if (isset(
                    $trackingDataIds['level5'])) {
                        $query->where('criticalActivityId', $trackingDataIds['level5']);
                    }

                })
                ->where(function ($query) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        return $query->where('endDate', '>=', Carbon::parse($startDate)->format(config('constants.dbDateFormat')))
                            ->where('endDate', '<=', Carbon::parse($endDate)->format(config('constants.dbDateFormat')));
                    }

                    return true;
                })
                ->orderBy('endDate', 'DESC')
                ->get();

            $response = ($response) ? $response->toArray() : $response;
        endif;

        $getData[$getActId] = $response;
        return $getData;
    }

    /**
     * This function will set end date using enddate and last tracking date
     * @param $getTrackDate, $getRangeDate
     * @return object
     */
    public function setEndDateUsingTrack($getTrackDate, $getRangeDate)
    {
        $trackDate = strtotime($getTrackDate);
        $endDate = strtotime($getRangeDate['dueDate']);
        $assignDate = strtotime($getRangeDate['assignDate']);
        if ($trackDate) {
            if ($trackDate > $endDate) {
                return $getRangeDate['dueDate'];
            } else if ($trackDate < $assignDate) {
                return $getRangeDate['dueDate'];
            } else {
                return $getTrackDate;
            }
        } else {
            return $getRangeDate['dueDate'];
        }

    }

    /**
     * This function will get tracking data on th basis of parameters set
     * @param $array, $type, $id
     * @return object
     */
    public function setTrackingData($array, $type, $id)
    {

        if (isset($array[$id])) {
            if ($type == 'actual') {
                return isset($array[$id][0]['trackingValue']) ? $array[$id][0]['trackingValue'] : '';
            }
            if ($type == 'tDate') {
                return isset($array[$id][0]['endDate']) ? $array[$id][0]['endDate'] : '';
            }
            if ($type == 'accumulate') {
                if (isset($array[$id][1]['trackingValue'])) {
                    return $array[$id][0]['trackingValue'] - $array[$id][1]['trackingValue'];
                } else {
                    return isset($array[$id][0]['trackingValue']) ? $array[$id][0]['trackingValue'] : '';
                }

            }
            if ($type == 'accumActual') {
                return isset($array[$id][0]['trackingValue']) ? $array[$id][0]['trackingValue'] : '';
            }
            if ($type == 'dateDiff') {
                return isset($array[$id][0]['trackingValue']) ? $array[$id][0]['trackingValue'] : '';
            }
            if ($type == 'totActual') {
                $value = 0;
                if (isset($array[$id])) {
                    foreach ($array[$id] as $val) {
                        $value += $val['trackingValue'];
                    }
                }
                return $value;
            }
        }
    }

    /**
     * This function will calculate runrate from actual and goal
     * @param $actual, $goal, $getDateRangeVal, $forTop = 0
     * @return object
     */
    public function getRunRateCal($actual, $goal, $getDateRangeVal, $forTop = 0)
    {
        $finalRunVal = 0;
        if ($goal != '' && $actual != '' && $actual > 0 && $goal > 0) {
            $ranRate = ($actual) / $goal;
        } else if ($goal < 0 || $actual < 0) {
            $ranRate = 0;
        } else {
            $ranRate = 0;
        }

        $finalRunVal = round($ranRate * 100);
        if ($forTop) {
            if ($getDateRangeVal['minPerformAv'] != '' && $finalRunVal <= $getDateRangeVal['minPerformAv']) {
                return $getDateRangeVal['minPerformAv'];
            } else if ($getDateRangeVal['maxPerformAv'] != '' && $finalRunVal >= $getDateRangeVal['maxPerformAv']) {
                return $getDateRangeVal['maxPerformAv'];
            } else {
                return $finalRunVal;
            }
        } else {
            return $finalRunVal;
        }

    }

    /**
     * This function will insert all dails data into database
     * @param $data, $masterkey, $key
     * @return object
     */
    public function getDialsInsertIntoDB($data, $masterkey, $key)
    {

        $getResult = AvatarAlert::select("*")
            ->where('ID', $data['id'])
            ->where('masterId', $masterkey)->get()->toArray();

        if (sizeof($getResult) == 0) {

            $date = date('Y-m-d', strtotime($data['dueDate']));
            $parent = $data['parent'];
            $name = $data['name'];

            $dataArray = array();
            $dataArray['activityType'] = $data['type'];
            $dataArray['userId'] = $data['userId'];
            $dataArray['parent'] = $parent;
            $dataArray['ID'] = $data['id'];
            $dataArray['name'] = $name;
            $dataArray['dueDate'] = $date;
            $dataArray['status'] = $data['status'];
            $dataArray['masterId'] = $masterkey;
            $dataArray['key'] = $key;
            $dataArray['is_confirm'] = "0";
            $dataArray['type'] = 2;
            AvatarAlert::insert($dataArray);

        }
    }

    /**
     * This function will get runrate for do-not accumulate activity type
     * @param $getActData, $getDateRangeVal, $totActual, $forTop = 0
     * @return object
     */
    public function getRunrateForAccumulate($getActData, $getDateRangeVal, $totActual, $forTop = 0)
    {
        $startDate = isset($getDateRangeVal['assignDate']) ? $getDateRangeVal['assignDate'] : date("Y-01-01");
        $endDate = isset($getDateRangeVal['dueDate']) ? $getDateRangeVal['dueDate'] : date("Y-12-31");

        if ($getDateRangeVal['assignDate'] == 'NULL' || $getDateRangeVal['assignDate'] == '') {
            $startDate = date("Y-01-01");
        }

        if ($getDateRangeVal['dueDate'] == 'NULL' || $getDateRangeVal['dueDate'] == '') {
            $endDate = date("Y-12-31");
        }

        $seasonal = 0;
        $finalRunVal = 0;
        $ts1 = strtotime($startDate);
        $ts2 = strtotime($endDate);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);
        $startMon = 0;
        $nexYrVal = 0;

        if (isset($getDateRangeVal['trackSign']) && $getDateRangeVal['trackSign'] == '@') {
            $seasonalGoalarr = explode(',', $getDateRangeVal['seasonalGoal']);

            if ($month1 > $month2) {
                for ($startMon = $month1; $startMon <= 12; $startMon++) {
                    $seasonal = isset($seasonalGoalarr[$startMon - 1]) ? trim($seasonalGoalarr[$startMon - 1]) : 0;
                }
                for ($nexYrVal = 1; $nexYrVal <= $month2; $nexYrVal++) {
                    $seasonal = isset($seasonalGoalarr[$nexYrVal - 1]) ? trim($seasonalGoalarr[$nexYrVal - 1]) : 0;
                }
            } else {
                foreach ($seasonalGoalarr as $key => $value) {
                    # code...
                    if ($key >= $month1 - 1 && $key <= $month2 - 1) {
                        if (isset($value) && $value != '') {
                            $seasonal = isset($value) ? trim($value) : 0;
                        }
                    }

                }
            }
            if (trim($totActual) == 0 || trim($totActual) == '' || trim($seasonal) == 0 || trim($seasonal) == '') {
                $finalRunVal = 0;
            } else {
                $finalRunVal = round($totActual / $seasonal * 100);
            }

        } else {
            if ($getDateRangeVal['goal'] != '' && $totActual != '') {
                $finalRunVal = round($totActual / $getDateRangeVal['goal'] * 100);
            } else {
                $finalRunVal = 0;
            }

        }
        if ($forTop) {
            if ($getDateRangeVal['minPerformAv'] != '' && $finalRunVal <= $getDateRangeVal['minPerformAv']) {
                return $getDateRangeVal['minPerformAv'];
            } else if ($getDateRangeVal['maxPerformAv'] != '' && $finalRunVal >= $getDateRangeVal['maxPerformAv']) {
                return $getDateRangeVal['maxPerformAv'];
            } else {
                return $finalRunVal;
            }
        } else {
            return $finalRunVal;
        }

    }

    /**
     * This function will process each activity dials data
     * @param $rows, $alertDataVal
     * @return response
     */
    public function performDialAction($rows, $alertDataVal)
    {

        $dataArray = array();

        $dataArray[] = $this->levels->getLevel2DialActivty($rows, $alertDataVal);

        $dataArray = array_filter($dataArray);

        return count($dataArray) ? $dataArray : false;
    }

    public function manageMessages($dataArray, $dataDialArrayVal)
    {
        $messageArray = array();

        $userData = array();
        $setting = array();

        $setting = $this->email->getCommunicationOption();

        if (count($dataArray)) {

            foreach ($dataArray as $keys => $values) {

                foreach ($values as $key => $val) {

                    $array = $this->getPreferredOptions($val['userId'], $val['delegate']);

                    foreach ($array as $index => $data) {

                        $type = $setting[$data['dueDate']];

                        $preferedDate = $this->date->getDateConvert($type, $data);

                        if ($this->date->is_valid($type, $preferedDate, $val['dueDate'], $data['startDate'], $data['endDate'])) {

                            if (!in_array($val['status'], $this->exclude) && $val['includeInAvatar'] == 1 && $val['completed'] != 1) {
                                $messageArray[$data['eID']][$val['dueDate']][$val['id']] = $val;
                                $key_array = explode(',', $val['delegate']);

                                foreach ($key_array as $index => $arx) {

                                    $outData = $this->getPreferredOptions($val['userId'], $arx);
                                    if ($this->date->is_valid($type, $preferedDate, $val['dueDate'], $data['startDate'], $data['endDate'])) {
                                        $userData[$val['userId'] . '-' . $arx] = $outData;
                                    }
                                }

                            }
                            // Store number and delegate for email top activity header.
                            if (!in_array($val['status'], $this->exclude) && $val['includeInProfile'] == 1 && $val['includeInAvatar'] == 1 && $val['completed'] != 1) {
                                if (!isset($this->userAllActivityCounter['activityCounter'][$val['userId'] . '-' . $data['eID']])) {
                                    $this->userAllActivityCounter['activityCounter'][$val['userId'] . '-' . $data['eID']] = 1;
                                    $this->userAllActivityCounter['activityCounter']['activity'][$val['userId'] . '-' . $data['eID']][] = $val;
                                } else {
                                    $this->userAllActivityCounter['activityCounter'][$val['userId'] . '-' . $data['eID']] = $this->userAllActivityCounter['activityCounter'][$val['userId'] . '-' . $data['eID']] + 1;
                                    $this->userAllActivityCounter['activityCounter']['activity'][$val['userId'] . '-' . $data['eID']][] = $val;
                                }
                            }

                        }
                    }
                }
            }
        } else {
            return $this->unprocessableApiResponse(__('core.emptyResponse'));
        }

        return $this->createMessages($messageArray, $setting, $userData, $dataDialArrayVal);
    }

    /**
     * this function will create the messages for the mail
     *
     * @param $messageArray
     * @param $setting
     * @param $userData
     * @param $dataDialArrayVal
     * @return void
     */
    public function createMessages($messageArray, $setting, $userData, $dataDialArrayVal)
    {
        $dataContent = array();
        $dialsContent = array();
        foreach ($messageArray as $key => $val) {

            $masterkey = md5(uniqid(rand(), true)) . strtotime(date('Y-m-d H:i:s'));

            $dataGet = $this->addMessages($key, $val, $masterkey, $setting, $userData);

            if (count($dataGet)) {

                foreach ($dataGet as $index => $array) {

                    foreach ($array as $ind => $value) {

                        if ($ind == 'keys') {
                            $dataContent['dataContent'][$array['keys']][$ind] = $value;
                        } else {
                            $dataContent['dataContent'][$array['keys']][$ind][] = $value;
                        }

                    }
                }
            }
            $dataContent[$key]['masterkey'] = $masterkey;
        }
        $dialsContent = $dataDialArrayVal;
        $userData = $this->get_users_delegate_data();

        if (!count($userData) && isset($dialsContent['users'])) {

            $userData = $dialsContent['users'];
        }

        return $this->getMergeContent($dialsContent, $dataContent, $setting, $userData);

    }

    public function addMessages($userId, $dataArray, $masterkey, $setting, $data)
    {

        $urldecode = $this->serverName;

        $delegate = array();
        $dataContent = array();
        $dataReturn = array();

        $alertData = $this->dials->getDelegateData($this->delegateName);
        foreach ($dataArray as $keys => $values) {
            if (count($values)) {

                foreach ($values as $key => $val) {
                    if (in_array($val['userId'], $alertData)) {

                        $key_array = explode(',', $userId);

                        foreach ($key_array as $index => $arx) {

                            if ($this->email->is_sender_valid($setting, $data, $val['userId'], $arx)) {

                                $key = md5(uniqid(rand(), true)) . strtotime(date('Y-m-d H:i:s'));
                                $result['parent'] = $val['parent'];

                                $result['url'] = $urldecode . 'group-update/key/' . $key;
                                $result['name'] = $val['name'];

                                $dataContent[$val['userId'] . '-' . $arx][$keys][] = $result;
                                $this->userActivityCounter++;

                                $getDueDates = $this->date->getDueDates($val['dueDate']);

                                if (!isset($this->dueDateCount[$arx][$getDueDates])) {
                                    $this->dueDateCount[$arx][$getDueDates] = 0;
                                }

                                $this->dueDateCount[$arx][$getDueDates] = ($this->dueDateCount[$arx][$getDueDates] + 1);

                                $this->getInsertIntoDB($val, $masterkey, $key);
                            }
                        }
                    }
                }
            }
        }

        $incr = 0;
        foreach ($dataContent as $keys => $values) {

            $dataReturn[$incr]['keys'] = $keys;
            $dataReturn[$incr]['values'] = $values;
            $incr++;
        }

        return $dataReturn;
    }

    /**
     * this function will save the masterkey in the database
     * @param $data, $masterkey, $key
     * @return array
     */
    public function getInsertIntoDB($data, $masterkey, $key)
    {

        $getResult = AvatarAlert::select("AID")
            ->where('ID', $data['id'])
            ->where('masterId', $masterkey)->get()->toArray();

        if (count($getResult) == 0) {
            $date = Carbon::parse($data['dueDate'])->format('Y-m-d');
            $parent = $data['parent'];
            $name = $data['name'];

            $dataArray = array();
            $dataArray['activityType'] = $data['type'];
            $dataArray['userId'] = $data['userId'];
            $dataArray['parent'] = $parent;
            $dataArray['ID'] = $data['id'];
            $dataArray['name'] = $name;
            $dataArray['dueDate'] = $date;
            $dataArray['status'] = $data['status'];
            $dataArray['masterId'] = $masterkey;
            $dataArray['key'] = $key;
            $dataArray['is_confirm'] = "0";
            $dataArray['type'] = 1;
            AvatarAlert::insert($dataArray);

        }
    }

    /**
     * get the delegate users data
     */
    public function get_users_delegate_data()
    {

        $userData = array();

        $response = $this->communication->getAlertData($this->delegateName);

        if (count($response)) {
            foreach ($response as $rows) {
                # code...
                if ($rows['userId']) {
                    $userData[$rows['userId'] . '-' . $rows['eID']][] = $rows;
                }

            }
        }
        return $userData;
    }

    public function getMergeContent($dialsContent, $dataContent, $setting, $userData)
    {

        $urldecode = $this->serverName;
        $dialsData = array();
        $manualNotifiArr = array();
        $activityData = array();
        if (count($userData)) {
            if (isset($dialsContent['dataContent'])) {
                $dialsData = $dialsContent['dataContent'];
            }

            if (isset($dataContent['dataContent'])) {
                $activityData = $dataContent['dataContent'];
            }

            foreach ($userData as $key => $value) {
                $getUsersDialsFinalId = array();
                $getUsersActDataFinalId = array();
                $content = [];
                if (isset($dialsData) && $dialsData) {

                    if (trim($setting[$userData[$key][0]['preferenceType']]) == 'Dashboard Dials' || trim($setting[$userData[$key][0]['preferenceType']]) == 'Both') {

                        $getUsersDialsFinalId = explode("-", $key);

                        if ($dialsData !== '') {

                            $content['dials']['mergingContent'] = $this->mergingContent($dialsData, $dialsContent[$getUsersDialsFinalId[1]]['masterkey'], 'Dials', $key, false);
                        }
                    }
                }

                if (isset($activityData[trim($key)]['values']) && $activityData[trim($key)]['values']) {

                    if (trim($setting[$userData[$key][0]['preferenceType']]) == 'Dashboard Activity' || trim($setting[$userData[$key][0]['preferenceType']]) == 'Both') {
                        if (isset($dataContent['dataContent'])) {
                            $getUsersActDataFinalId = explode("-", $key);

                            if ($activityData[$key] != '') {

                                $content['activity']['mergingContent'] = [
                                    'type' => 'Activity',
                                    'getActivity' => $this->getActivityCount($this->dueDateCount[$getUsersActDataFinalId[1]]),
                                    'url' => $urldecode . 'group-update/masterId/' . $dataContent[$getUsersActDataFinalId[1]]['masterkey'],
                                    'values' => $activityData[$key],

                                ];
                            }

                        }
                    }
                }

                $getUsersDialsFinalId = explode("-", $key);
                if ($content != '') {

                    if (!isset($dialsContent[$getUsersDialsFinalId[1]]['masterkey'])) {
                        $dialsContent[$getUsersDialsFinalId[1]]['masterkey'] = '';
                    }
                    if (!isset($dataContent[$getUsersDialsFinalId[1]]['masterkey'])) {
                        $dataContent[$getUsersDialsFinalId[1]]['masterkey'] = '';
                    }
                    // If Only links needed and email or sms not needs to send.
                    if ($this->notficationLinkCheck) {

                        $finalLinkArr = array();
                        $finalLinkArr['dial'] = $dialsContent[$getUsersDialsFinalId[1]]['masterkey'] ? $urldecode . 'group-update/masterId/' . $dialsContent[$getUsersDialsFinalId[1]]['masterkey'] : 'No Dials';
                        $finalLinkArr['activity'] = $dataContent[$getUsersDialsFinalId[1]]['masterkey'] ? $urldecode . 'group-update/masterId/' . $dataContent[$getUsersDialsFinalId[1]]['masterkey'] : 'No Activities';
                        $manualNotifiArr['links'] = $finalLinkArr;
                        return $manualNotifiArr;
                    } else {


                            $finalLinkArr = array();
                            $finalLinkArr['dial'] = $dialsContent[$getUsersDialsFinalId[1]]['masterkey'] ? $urldecode . 'group-update/masterId/' . $dialsContent[$getUsersDialsFinalId[1]]['masterkey'] : 'No Dials';
                            $finalLinkArr['activity'] = $dataContent[$getUsersDialsFinalId[1]]['masterkey'] ? $urldecode . 'group-update/masterId/' . $dataContent[$getUsersDialsFinalId[1]]['masterkey'] : 'No Activities';

                            if(!empty($dialsContent[$getUsersDialsFinalId[1]]['masterkey'])) {
                                $array = explode('-', $key);
                                $urldecode = $this->serverName;
                                $userId = $array[1];
                                $degatelinkAvatar = '/myavatar/' . $userId;

                                $manualNotifiArr['success'] = true;
                                $fromEmail = "ProAdvisor Drivers";
                                $subject = "ProAdvisor Drivers links";
                                $email = '';
                                if ($this->delegateEmail) {
                                    $email = $this->delegateEmail;
                                }

                                // dd($urldecode);

                                $this->initializeSending($content, $key, $dialsContent[$getUsersDialsFinalId[1]]['masterkey'], $dataContent[$getUsersDialsFinalId[1]]['masterkey'], '', $userData, $email);
                                $manualNotifiArr['success'] = true;

                                return $manualNotifiArr;

                            } else {
                                return [
                                    'status' => false,
                                    'message' => 'Delegate has no data'
                                ];
                            }
                        }
                    // }
                }
            }

        }
    }

    public function mergingContent($dataContent, $masterkey, $type, $users, $unit)
    {

        $urldecode = $this->serverName;
        $array = explode('-', $users);

        $content = [];
        if ($type == 'Dials') {
            $type = 'DIALS';
        }

        if ($type == 'Activity') {
            $type = 'ACTIVITY';
        }

        $content['type'] = $type;
        $content['url'] = $urldecode . 'group-update/masterId/' . $masterkey;

        foreach ($dataContent as $key => $val) {

            if ($type == 'ACTIVITY') {
                $content['dueDate'] = $key;
            }

            foreach ($val as $idx => $out) {
                if (is_array($out)) {

                    foreach ($out as $ind => $indval) {
                        $content['values'][] = $indval;

                    }
                }

            }
        }

        return $content;
    }

    /**
     * this function will get the activity count
     *
     * @param $dueDateCount
     * @return array
     */
    public function getActivityCount($dueDateCount)
    {

        $data = [];

        $data['twenty'] = isset($dueDateCount[20]) ? $dueDateCount[20] : 0;
        $data['forty'] = isset($dueDateCount[40]) ? $dueDateCount[40] : 0;
        $data['sixty'] = isset($dueDateCount[60]) ? $dueDateCount[60] : 0;
        $data['eighty'] = isset($dueDateCount[80]) ? $dueDateCount[80] : 0;
        $data['hundred'] = isset($dueDateCount[100]) ? $dueDateCount[100] : 0;

        return $data;
    }

    public function getPreferredOptions($id, $delegate)
    {

        if (!is_array($delegate)) {
            $delegate = explode(',', $delegate);
        }

        $array = array();

        $getResult = CommunicationOption::where('userId', $id)
            ->wherein('eID', $delegate)
            ->where('cPreference', '!=', Config::get('statistics.cPreference'))
            ->whereNotNull('cPreference')->get()->toArray();

        foreach ($getResult as $rows) {
            $array[] = $rows;
        }
        return $array;
    }

    /**
     * this function prepares the data for the mail
     *
     * @param $content
     * @param $keys
     * @param $dialsMasterKey
     * @param $masterkey
     * @param $urldecode
     * @param $data
     * @return void
     */
    public function initializeSending($content, $keys, $dialsMasterKey, $masterkey, $urldecode, $data, $email)
    {

        // dd($email);
        $array = explode('-', $keys);

        $userId = $array[1];
        $urldecode = $this->serverName;
        // $userName = $this->email->getUserName($array[0]);
        $activityCountDetails = '';
        $activityDialCountDetails = '';
        $activityCountDetails = isset($this->userAllActivityCounter['activityCounter']['activity'][$keys]) ? $this->userAllActivityCounter['activityCounter']['activity'][$keys] : 0;
        $activityDialCountDetails = isset($this->userDialActivityCounter['DialCounter']['activity'][$keys]) ? $this->userDialActivityCounter['DialCounter']['activity'][$keys] : 0;
        $getOverAllPer = 0;
        $averageActivityPer = 0;
        $activityCount = isset($this->userAllActivityCounter['activityCounter'][$keys]) ? $this->userAllActivityCounter['activityCounter'][$keys] : 0;
        $dialCount = isset($this->userDialActivityCounter['DialCounter'][$keys]) ? $this->userDialActivityCounter['DialCounter'][$keys] : 0;
        if ($activityCountDetails) {
            foreach ($activityCountDetails as $value) {
                # code...
                if ($value['dueDate']) {
                    $getOneActivityPer = $this->date->getDueDates($value['dueDate']);
                    $getOverAllPer = $getOverAllPer + $getOneActivityPer;
                }
            }
        }
        if ($activityCount == 0) {
            $averageActivityPer = 0;
        } else {
            $averageActivityPer = $getOverAllPer / $activityCount;
        }

        $getDialOverAllPer = 0;
        $averageDialActivityPer = 0;
        if ($activityDialCountDetails) {
            foreach ($activityDialCountDetails as $value) {
                # code...
                if (isset($value['finalRunrate'])) {
                    $getDialOneActivityPer = $value['finalRunrate'];
                    $getDialOverAllPer = $getDialOverAllPer + $getDialOneActivityPer;
                }
            }
        }
        if ($dialCount == 0) {
            $averageDialActivityPer = 0;
        } else {
            $averageDialActivityPer = $getDialOverAllPer / $dialCount;
        }

        $getActDialImages = $this->email->getImageBasedOnPer($averageActivityPer, $averageDialActivityPer, $userId);

        $header = [];

        if ($activityCount) {
            $checkActSec = '';
            $header['healthScore'] = $getActDialImages['gradeActVal'];
            $header['activityTitle'] = $getActDialImages['actTitle'];
            $header['activityCount'] = $activityCount;

            $header['imageUrl'] = $getActDialImages['activity'];
            $header['averageActivityPer'] = round($averageActivityPer);

        }

        if ($dialCount) {

            $header['performance'] = $getActDialImages['gradeDialVal'];
            $header['dialTitle'] = $getActDialImages['dialTitle'];
            $header['dialCount'] = $dialCount;
            $header['dialUrl'] = $getActDialImages['dial'];
            $header['averageDialActivityPer'] = round($averageDialActivityPer);

        }

        $content = array_merge($content, $header);

        $footer = '';
        $masterkey = ($masterkey) ? $masterkey : -1;
        $dialsMasterKey = ($dialsMasterKey) ? $dialsMasterKey : -1;
        // dial parmeter is third one
        $mcontent = array();
        $mcontent['activity'] = $urldecode . 'group-update/masterId/' . $masterkey;
        $mcontent['dial'] = $urldecode . 'group-update/masterId/' . $dialsMasterKey;

        if ($dialCount || $activityCount) {

            if($email != null) {
                $fromEmail = "Proadvisor Drivers";
                $subject = "Proadvisor Driver links";
                $this->email->forwardEmail( $fromEmail, $email, $subject, $content, '', '');
            } else {
                $this->email->emailToUser($content, $data, $array[0], $mcontent, $array[1]);
            }
            $this->userActivityCounter = 0;
            $this->userTemplateHeader = 0;
        }

    }
}
