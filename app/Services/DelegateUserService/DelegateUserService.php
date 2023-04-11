<?php

namespace App\Services\DelegateUserService;

use App\Models\Profile\CommunicationOption;
use App\Models\Profile\Profile;
use App\Models\Tracking\DelegateUser;
use App\Models\User;
use App\Traits\ApiResponse;
use Config;
use DB;
use Illuminate\Support\Facades\Auth;

class DelegateUserService
{

    use ApiResponse;

    /**
     * this function will save the delegates and if the delegate exists it will update the delegate's
     * data if he is a virtual delegate or existing user delegate
     * @param $userId, $parentId, $isDelegate
     * $isDelegate = 1 => delegate
     * @return JSON
     */
    public static function saveNewDelegate($userId, $parentId, $isDelegate)
    {
        $saveDelegate = DelegateUser::createDelegate($userId, $parentId, $isDelegate);
        return $saveDelegate;
    }

    /**
     * this function will add the delegate
     * @param $name
     */
    public static function addUserWithDelegate($name)
    {
        $user = User::delegateUsers($name);

        if ($user) {

            $checkDelegateExists = DelegateUser::where('userId', $user->user_id)
                ->where('parentId', Auth::user()->user_id)->exists();

            if (!$checkDelegateExists) {

                Profile::saveDelegateProfile($name, $user->user_id);
                $saveDelegateResponse = self::saveNewDelegate($user->user_id, Auth::user()->user_id, false);

                return $saveDelegateResponse;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * function is used to get all stored information regarding delegate.
     *
     * @param int $userId : logged user id
     * @param int $delegateUserId delegate user id related to logged user.
     */
    public function getCommunicationOptionsForDelegates($userId, $delegateUserId)
    {
        $options = CommunicationOption::where(['userId' => $userId, 'eID' => $delegateUserId]);
        if ($options->count()) {
            return $options->select(
                '*', DB::raw(
                    "DATE_FORMAT(startDate, '" . Config::get('constants.dbDateConvert') . "') as 'startDate', " .
                    "DATE_FORMAT(endDate, '" . Config::get('constants.dbDateConvert') . "') as 'endDate' "
                ))->first()->toArray();
        }

        return array();
    }

    /**
     * this function will update the delegate details
     * @param $data
     * @return JSON
     */
    public function updateDelegate($data, $id)
    {
        $response = ['status' => false, 'message' => ''];
        if (!empty(Auth::user()->user_id)) {
            if (!empty($id)) {

                if (!$this->verifyDuplicateEmail($id, $data['email'])) {
                    $response['message'] = __('core.emailExists');
                    return $response;
                }
                $checkCommunicationExists = CommunicationOption::where('userId', Auth::user()->user_id)
                    ->where('eID', $id);

                if (!$checkCommunicationExists->count()) {
                    $saveCommunication = CommunicationOption::createCommunication($data, $id);
                } else {
                    $checkCommunicationExists->update([
                        'frequency' => $data['frequency'],
                        'cPreference' => $data['preferedCommunication'],
                        'preferenceType' => $data['preferenceType'],
                        'email' => $data['email'],
                        'cell' => $data['cell'],
                        'dayTime' => $data['dayTime'],
                        'dueDate' => $data['dueDate'],
                        'dateRange' => $data['dateRange'] ? $data['dateRange'] : 0,
                        'startDate' => $data['startDate'],
                        'endDate' => $data['endDate'],
                    ]);

                    $communicationOption = $checkCommunicationExists->first();
                    $response['status'] = true;
                    $response['message'] = __('core.delegateUpdateSuccess');
                }

                $responseData = $this->updateDelegateUserData($data, $id);
            }
        }

        return $response;
    }

    /**
     * this function will update the delegate user's data and also the profile
     */
    public function updateDelegateUserData($data, $id)
    {
        $user = User::where('user_id', $id)
            ->where('role_id', (int) Config::get('statistics.delegateUserType'));

        $response = ['status' => false, 'data' => array()];
        if ($user->count()) {
            $userData = $user->first();
            $userData->name = $data['name'];
            $userData->email = $data['email'];

            if ($userData->save()) {
                $response['status'] = true;
                $response['data']['name'] = $data['name'];
            }
        }

        if (User::where('user_id', $id)->count()) {
            Profile::updateDelegateProfile($id, $data['cell'], $data['name']);
        }

        return $response;
    }

    /**
     * This function will delete the delegate along with its communication details
     * @param $id
     * @return bool
     */
    public function deleteDelegateData($id)
    {

        $delegateDelete = DelegateUser::where('delegateUsersId', $id)->where('parentId', Auth::user()->user_id);

        if ($delegateDelete->exists()) {
            $delegateUser = $delegateDelete->first();

            if (Auth::user()->user_id != $delegateUser['userId']) {
                $deleted = $delegateDelete->delete();

                if ($deleted) {
                    CommunicationOption::where('userId', Auth::user()->user_id)
                        ->where('eid', $delegateUser['userId'])
                        ->delete();
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * this function will update the status of the delegate from active to inactive or vice-versa
     * @param $data
     * @return bool
     */
    public function UpdateDelegateStatus($status, $delegateId)
    {
        $updateStatus = DelegateUser::find($delegateId)->update(['status' => $status]);

        if ($updateStatus) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * this function will check if the email is duplicate or not
     * @param $userId, $email
     * @return bool
     */
    public function verifyDuplicateEmail($userId, $email)
    {
        $check = User::where('email', $email)->where('user_id', '!=', $userId);

        if ($check->count() > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * for assigning the delegates to the user
     *
     * @return void
     */
    public function assignDelegateToUser($userId, $parentId, $isDelegate)
    {
        $delegateUser = DelegateUser::where('userId', $userId)->where('parentId', $parentId);
        $delegateExists = $delegateUser->exists();

        if(!$delegateExists) {
            $delegateSave = new DelegateUser;
            $delegateSave->userId = $userId;
            $delegateSave->parentId = $parentId;
            $delegateSave->isDelegate = $isDelegate;
            return $delegateSave->save();
        } else if($delegateExists) {
            return $delegateUser->update(['isDelegate' => true]);
        } else {
            return false;
        }
    }

    /**
     * get the delegates for auto suggestion
     *
     * @param Request $request
     * @return void
     */
    public function getDelegates($data)
    {
        $getAssignedDelegates = DelegateUser::where('parentId', Auth::user()->user_id)->with('user')->pluck('userId')->toArray();

        $users = User::where('name', 'LIKE', '%' . $data . '%')->whereNotIn('role_id', [Config::get('statistics.encqualifierType')])->whereNotIn('user_id', $getAssignedDelegates)
            ->get(['user_id', 'name'])->toArray();

        return $users;

    }
}
