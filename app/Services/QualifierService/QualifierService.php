<?php

namespace App\Services\QualifierService;

use App\Models\ENCQualifier;
use App\Models\Profile\Profile;
use App\Models\User;
use App\Traits\ApiResponse;
use Auth;
use Config;

class QualifierService
{

    use ApiResponse;

    public function addUserWithQualifier($qualifierName)
    {

        if (User::where('name', trim($qualifierName))->exists()) {
            return $this->unprocessableApiResponse(__('core.qualifierExists'));
        }
        $user = new User();
        $user->name = $qualifierName;
        $user->role_id = (int) Config::get('statistics.encqualifierType');
        $user->user_name = $qualifierName . '_' . time();
        $user->email = '';
        $user->password = 1;
        $user->description = '';
        $user->status = '';
        $user->dialNumber = '';
        $user->accessCode = '';
        $user->meetingLink = '';
        $user->isCompleted = 0;
        $user->remember_token = '';

        if ($user->save()) {
            $profile = new Profile();

            $profile->userId = $user->user_id;
            $profile->firstName = $qualifierName;
            $profile->save();

            $saveQualifier = self::saveQualifier($user->user_id, Auth::user()->user_id, 1);

            if (!$saveQualifier) {
                return $this->unprocessableApiResponse(__('core.userAddedButQualifierFailed'));
            }
        }
    }

    /**
     * this function will save the qualifier
     *
     * @param $userId
     * @param $parentId
     * @param $isQualifier
     * @return void
     */
    public function saveQualifier($userId, $parentId, $isQualifier)
    {
        $qualifierQuery = ENCQualifier::where('userName', $userId)->where('parentId', $parentId);

        $qualifierExist = $qualifierQuery->exists();

        if (!$qualifierExist) {
            $qualifier = new ENCQualifier();
            $qualifier->userName = $userId;
            $qualifier->parentId = $parentId;
            $qualifier->isQualifier = $isQualifier;

            if ($qualifier->save()) {
                return $this->successApiResponse(__('core.qualifierSaved'), $qualifier);
            } else {
                return $this->unprocessableApiResponse(__('core.qualifierNotSaved'));
            }

        } else if ($qualifierExist) {
            if ($qualifierQuery->update(['isQualifier' => 1])) {
                $qualifier = $qualifierUser->get()->first();
                return $this->successApiResponse(__('core.qualifierSaved'), $qualifier);
            } else {
                return $this->unprocessableApiResponse(__('core.qualifierExists'));
            }
        }
    }

    public function createQualifier($userId, $qualifierName)
    {
        if (!is_null(Auth::user()->user_id)) {
            if (!is_null($userId)) {
                return self::saveQualifier($userId, Auth::user()->user_id, 1);
            } else {
                return self::addUserWithQualifier($qualifierName);
            }
        }
    }

    /**
     * this function will update the qualifier details
     *
     * @param [type] $id
     * @param [type] $name
     * @return void
     */
    public function updateQualifier($id, $name, $status)
    {
        $qualifierExists = ENCQualifier::where('userName', $id)->where('parentId', Auth::user()->user_id)->first();
        if($qualifierExists->doesntExist())
            return $this->unprocessableApiResponse(__('core.qualifierNotExists'));

        if ($name != null) {
            $user = User::findOrFail($id);
            if ($user->role_id != Config::get('statistics.encqualifierType')) {
                return $this->unprocessableApiResponse(__('core.qualifierPermissionError'));
            }

            $user->name = $name;
            if ($user->save()) {
                return $this->successApiResponse(__('core.qualifierUpdated'), $user);
            } else {
                return $this->unprocessableApiResponse(__('core.qualifierNotUpdated'));
            }
        }else if(isset($status)) {
            $qualifierStatus = $qualifierExists->update(['status' => ($qualifierExists->status == 1) ? 0 : 1]);
            return $this->successApiResponse('status updated successfully');
        }
    }

    public function deleteQualifier($id)
    {
        $qualifier = ENCQualifier::where('ENCQualifierId', $id)->where('parentId', Auth::user()->user_id);

        if (!$qualifier->exists()) {
            return $this->unprocessableApiResponse(__('core.qualifierNotExists'));
        } else {
            $qualifier->delete();

            return $this->successApiResponse(__('core.qualifierDeleted'));
        }
    }
}
