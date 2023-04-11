<?php

namespace App\Services\NotificationService;

use App\Models\User;
use App\Models\AvatarDetails;
use App\Mail\TrackingDataMail;
use Illuminate\Support\Facades\Config;
use App\Models\Profile\CommunicationOption;
use App\Models\Notification\NotificationVerify;
use App\Models\Notification\CommunicationSetting;

class EmailService
{

    public $serverName = 'staging.padapi.rubico.dev';

    public function __construct()
    {
        $this->communication = new CommunicationOption();
    }

    public function emailToUser($content, $data, $userId, $mcontent = '', $delegate)
    {

        $setting = $this->getCommunicationOption();
        $preferedData = $this->getPreferredOptionForEmail($userId, $delegate);
        $preference = $preferedData['cPreference'];

        $preference = $setting[$preference];

        $toEmail = $preferedData['email'];
        $userEmails = array();
        $userEmails = $this->checkFinalUserData($delegate);

        if (in_array($toEmail, $userEmails)) {
            if ($this->getCompletedNotification($userId, $delegate) == 0 && $content != '') {

                if ($this->is_sender_valid($setting, $data, $userId, $delegate, 1)) {

                    if ($preference == 'Both') {

                        return $this->proceedMail($toEmail, $content, $userId, $delegate);
                    } elseif ($preference == 'Email') {

                        return $this->proceedMail($toEmail, $content, $userId, $delegate);
                    }
                }
            }
        }
    }

    public function getPreferredOptionForEmail($id, $delegate)
    {

        $getResult = CommunicationOption::where('userId', $id)
            ->wherein('eID', [$delegate])->first()->toArray();

        if (isset($getResult)) {
            return $getResult;
        }

        return '';

    }

    /**
     * This function will send notifications to users
     * @param NA
     * @return object
     */
    public function proceedMail($toEmail, $content, $userId, $delegate)
    {

        $fromEmail = "ProAdvisor Drivers";
        $userName = $this->getUserName($userId);
        $delegateNameVal = $this->getUserName($delegate);
        $subject = "ProAdvisor Drivers Notifications for " . $delegateNameVal . ' - ' . date("F d, Y ") . ' - ' . $userName;

        return $this->forwardEmail($fromEmail, $toEmail, $subject, $content, $userId, $delegate);
    }

    /**
     * this function will forward email to the user
     */
    public function forwardEmail($from, $to, $subject, $message, $userId, $delegate)
    {
        $data['to'] = $to;
        $data['subject'] = $subject;
        $data['content'] = $message;
        return \Mail::to($data['to'])->send(new TrackingDataMail($data['subject'], $data['content']));
    }

    /**
     * this function gives the dial image according to the percentage
     *
     * @param $cARate
     * @param $getDialPer
     * @return array
     */
    public function getImageBasedOnPer($cARate, $getDialPer, $userId)
    {

        $setActImg = '';
        $setDialImg = '';
        $avatarTitle = '';
        $avatarDialTitle = '';
        $getGradeVal = '';
        $getDialGradeVal = '';

        if ($cARate >= 90) {
            $healthId = Config::get('constants.healthIds.five');

            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);

            if(!is_null($avatarImage)) {
                $setActImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            } else{
                $setActImg = '/images/icon1.png';
            }
            $avatarTitle = 'Outstanding';
            $getActGradeVal = 'A';
        } elseif ($cARate <= 89.99 && $cARate >= 80) {
            $healthId = Config::get('constants.healthIds.four');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);

            if(!is_null($avatarImage)) {

                $setActImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon2.png';
            }
            $avatarTitle = 'Good';
            $getActGradeVal = 'B';
        } elseif ($cARate <= 79.99 && $cARate >= 60) {
            $healthId = Config::get('constants.healthIds.three');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);

            if(!is_null($avatarImage)) {
                $setActImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon3.png';
            }
            $avatarTitle = 'Fair';
            $getActGradeVal = 'C';
        } elseif ($cARate <= 59.99 && $cARate >= 40) {
            $healthId = Config::get('constants.healthIds.two');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);

            if(!is_null($avatarImage)) {
                $setActImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon4.png';
            }
            $avatarTitle = 'Poor';
            $getActGradeVal = 'D';
        } else {
            $healthId = Config::get('constants.healthIds.one');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);
            if(!is_null($avatarImage)) {

                $setActImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon5.png';
            }
            $avatarTitle = 'Sick';
            $getActGradeVal = 'F';
        }

        if ($getDialPer >= 90) {
            $healthId = Config::get('constants.healthIds.five');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);
            if(!is_null($avatarImage)) {
                $setDialImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon1.png';
            }
            $avatarDialTitle = 'Outstanding';
            $getDialGradeVal = 'A';
        } elseif ($getDialPer <= 89.99 && $getDialPer >= 80) {
            $healthId = Config::get('constants.healthIds.four');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);

            if(!is_null($avatarImage)) {
                $setDialImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon2.png';
            }
            $avatarDialTitle = 'Good';
            $getDialGradeVal = 'B';
        } elseif ($getDialPer <= 79.99 && $getDialPer >= 60) {
            $healthId = Config::get('constants.healthIds.three');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);
            if(!is_null($avatarImage)) {
                $setDialImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon3.png';
            }
            $avatarDialTitle = 'Fair';
            $getDialGradeVal = 'C';
        } elseif ($getDialPer <= 59.99 && $getDialPer >= 40) {
            $healthId = Config::get('constants.healthIds.two');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);
            if(!is_null($avatarImage)) {
                $setDialImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon4.png';
            }
            $avatarDialTitle = 'Poor';
            $getDialGradeVal = 'D';
        } else {
            $healthId = Config::get('constants.healthIds.one');
            $avatarImage = $this->getAvatarHealthImage($userId, $healthId);
            if(!is_null($avatarImage)) {
                $setDialImg = 'https://avataaars.io/?accessoriesType='.$avatarImage->accessoriesType.'&avatarStyle=Transparent&clotheColor='.$avatarImage->clotheColor.'&clotheType='.$avatarImage->clotheType.'&eyeType='.$avatarImage->eyeType.'&eyebrowType='.$avatarImage->eyebrowType.'&facialHairType='.$avatarImage->facialHairType.'&hairColor='.$avatarImage->hairColor.'&mouthType='.$avatarImage->mouthType.'&skinColor='.$avatarImage->skinColor.'&topType='.$avatarImage->topType.'';
            }else{
                $setActImg = '/images/icon5.png';
            }
            $avatarDialTitle = 'Sick';
            $getDialGradeVal = 'F';
        }
        $performImages['activity'] = $setActImg;
        $performImages['dial'] = $setDialImg;
        $performImages['actTitle'] = $avatarTitle;
        $performImages['dialTitle'] = $avatarDialTitle;
        $performImages['gradeActVal'] = $getActGradeVal;
        $performImages['gradeDialVal'] = $getDialGradeVal;

        return $performImages;

    }

    /**
     * this function get the username of the user
     *
     * @param $id
     * @return string
     */
    public function getUserName($id)
    {
        $result = User::select('profile.firstname', 'profile.lastname')
            ->join('profile', 'users.user_id', '=', 'profile.userId')
            ->where('users.user_id', $id)->first()->toArray();

        return $result['firstname'] . ' ' . $result['lastname'];
    }

    public function checkFinalUserData($delegateName)
    {

        $userEmails = array();
        $response = $this->communication->getAlertData($delegateName);

        if (count($response)) {
            foreach ($response as $rows) {

                $userEmails[] = $rows['email'];
            }
        }
        return $userEmails;
    }

    /**
     * this function get the communication details of the user
     *
     * @return array
     */
    public function getCommunicationOption()
    {

        $getResult = array();
        $getResult = CommunicationSetting::get()->toArray();

        foreach ($getResult as $row) {
            if ($row['status'] > 0) {
                $array[$row['CID']] = $row['options'] . '~' . $row['status'];
            } elseif ($row['status'] != null) {
                $array[$row['CID']] = $row['options'] . '_' . $row['status'];
            } else {
                $array[$row['CID']] = $row['options'];
            }
        }
        return $array;
    }

    /**
     * this function gives the notification
     *
     * @param $userId
     * @param $delegateId
     * @return void
     */
    public function getCompletedNotification($userId, $delegateId)
    {
        $getResult = NotificationVerify::where('userId', $userId)
            ->where('delegateId', $delegateId)
            ->where('sentdate', date('Y-m-d'))->get()->toArray();
        return count($getResult);
    }

    /**
     * checks whether the sender is valid or not
     */
    public function is_sender_valid($setting, $data, $userId, $delegate, $trial = 0)
    {

        $delta = $data[$userId . '-' . $delegate];
        $delta[0]['frequency'] = '9';

        if (!isset($delta[0]['frequency']) || $delta[0]['frequency'] == 0) {
            $delta[0]['frequency'] = '9';
        }

        $array = explode('~', $setting[$delta[0]['frequency']]);
        //aprint($array);
        $currentDate = date('N');

        if ($array[1] != '') {

            $target = explode(',', $array[1]);

            if (isset($target[1]) && $target[1] != '') {

                if ($currentDate >= trim($target[0]) && $currentDate <= trim($target[1])) {
                    return true;
                }
                return false;
            } elseif ($target[0] != '' && !isset($target[1])) {

                $day = trim($target[0]);
                if ($day == $currentDate) {
                    return true;
                }

                return false;
            }
        }

        return false;
    }

    /**
     * get the avatar image according to the score
     *
     * @param [type] $userId
     * @param [type] $healthId
     * @return void
     */
    public function getAvatarHealthImage($userId, $healthId) {
        return AvatarDetails::where('userId', $userId)->where('healthId', $healthId)->first();
    }
}
