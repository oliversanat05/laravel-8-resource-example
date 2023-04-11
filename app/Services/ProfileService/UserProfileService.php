<?php

namespace App\Services\ProfileService;

use App\Models\Profile\Address;
use App\Models\Profile\Profile;
use App\Models\Profile\WorkHistory;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Config;
use Storage;

class UserProfileService
{

/**
 * for updating the profile data
 *
 * @param $data
 * @return array
 */
    public static function updateProfileData($data)
    {
        $profileData = [
            'anniversary' => Carbon::parse($data['anniversary'])->format(Config::get('constants.dbDateFormat')),
            'primaryBusiness' => $data['primaryBusiness'],
            'companyName' => $data['companyName'],
            'businessActivities' => $data['businessActivities'],
            'businessRole' => $data['businessRole'],
            'businessReasonForChoosing' => $data['dayOffActivity'],
            'secondaryBusiness' => $data['secondaryBusiness'],
            'business2Name' => $data['business2Name'],
            'business2Activites' => $data['business2Activites'],
            'business2Roles' => $data['business2Roles'],
            'business2ReasonForChoosing' => $data['business2ReasonForChoosing'],
            'businessEndeavors' => $data['businessEndeavors'],
            'birthPlace' => $data['birthPlace'],
            'maritalStatus' => $data['maritalStatus'],
            'spouseName' => $data['spouseName'],
            'religion' => $data['religion'],
            'dayOffActivity' => $data['dayOffActivity'],
            'favoriteVacation' => $data['vacationPlace'],
            'favoriteMovie' => $data['favoriteMovie'],
            'favoriteBook' => $data['favoriteBook'],
            'children' => $data['childrenName'],
            'pets' => $data['petsName'],
            'educationalPrep' => $data['educationPreparation'],
            'undergrad' => $data['undergraduate'],
            'hobbies' => $data['otherHobbies'],
            'organizations' => $data['organization'],
            'timeZoneId' => $data['timeZoneId'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'cellPhone' => $data['cellPhone'] ? $data['cellPhone'] : 0,
            'prefPhone' => $data['prefCellPhone'],
            'dateOfBirth' => Carbon::parse($data['dateOfBirth'])->format(Config::get('constants.dbDateFormat')),
            'fax' => $data['fax'],
        ];

        $checkProfileExists = Profile::where('userId', Auth::user()->user_id)->exists();

        if ($checkProfileExists) {
            Profile::where('userId', Auth::user()->user_id)->update($profileData);
            User::where('user_id', Auth::user()->user_id)->update([
                'name' => $data['firstName'] . ' ' . $data['lastName'],
            ]);
        } else {
            return false;
        }

    }

    /**
     * for updating the profile address
     *
     * @param $data
     * @return boolean
     */
    public static function updateProfileAddress($data)
    {
        $addresses = [
            [
                'streetAddress1' => $data['mailStreetPO'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip'],
                'addressTypeId' => 1,
            ],
            [
                'streetAddress1' => $data['shipAddress'],
                'addressTypeId' => 2,
            ],
            [
                'streetAddress1' => $data['resAddress'],
                'addressTypeId' => 3,
            ],
            [
                'streetAddress1' => $data['businessStreetPO'],
                'city' => $data['businessCity'],
                'state' => $data['businessState'],
                'zip' => $data['businessZip'],
                'addressTypeId' => 4,
            ],
            [
                'streetAddress1' => $data['secondBusinessStreetPO'],
                'city' => $data['secondBusinessCity'],
                'state' => $data['secondBusinessState'],
                'zip' => $data['secondBusinessZip'],
                'addressTypeId' => 5,
            ],
        ];

        foreach ($addresses as $key => $address) {

            $checkAddressExists = Address::where('profileId', Auth::user()->user_id)->where('addressTypeId', $address['addressTypeId'])->first();

            if ($checkAddressExists) {
                Address::where('profileId', Auth::user()->user_id)->where('addressTypeId', $address['addressTypeId'])->update($address);
            } else {
                $address['profileId'] = Auth::user()->user_id;
                Address::create($address);
            }
        }

        return true;
    }

    /**
     * for updating the workhistory of the user
     *
     * @param $data
     * @return void
     */
    public function profileWorkHistory($data)
    {
        $workHistoryData = [];
        $address = WorkHistory::where('profileId', Auth::user()->user_id)->get()->toArray();

        $dispOrder = [];
        foreach ($address as $key => $displayOrder) {
            $dispOrder[$key]['displayOrder'] = $displayOrder['displayOrder'];
        }

        foreach ($data as $key => $value) {

            if (count($address)) {
                $work = WorkHistory::where('profileId', Auth::user()->user_id)->where('displayOrder', $dispOrder[$key])->update($value);
            } else {

                $value['profileId'] = Auth::user()->user_id;
                $value['displayOrder'] = ++$key;
                WorkHistory::create($value);
            }
        }

        return true;

    }

    /**
     * for uploading the user profile image
     *
     * @ $data
     * @return void
     */
    public function uploadProfileImage($data)
    {
        $userImage = User::find(Auth::user()->user_id);

        $image = $data;

        $extension = $image->getClientOriginalExtension();
        $name = $image->getClientOriginalName();

        $newImageName = Auth::user()->user_name . '.' . $extension;

        $storage = Storage::disk('pics')->putFileAs(null, $image, $newImageName);

        $userImage->user_image = $newImageName;

        $imageUrl = env('APP_URL') . 'storage/pics/'. $userImage->user_image;

        if ($userImage->save()) {
            return $imageUrl;
        } else {
            return false;
        }
    }

    public function updateUserProfileData($profileData, $userData)
    {
        /**
         * update the user data
         */
        $saveUserData = User::where('user_id', Auth::user()->user_id)->update([
            'name' => $userData['firstName'] . ' ' . $userData['lastName'],
        ]);

        if ($saveUserData) {
            return true;
        } else {
            return false;
        }
    }
}
