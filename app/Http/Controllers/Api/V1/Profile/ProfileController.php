<?php

namespace App\Http\Controllers\Api\V1\Profile;

use DB;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Profile\Address;
use App\Models\Profile\Profile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileRequest;
use App\Services\ProfileService\UserProfileService;
use App\Http\Requests\ProfileImage\ProfileImageRequest;

class ProfileController extends Controller
{

    use ApiResponse;
    public $profile;

    public function __construct()
    {

        $this->profile = new UserProfileService();

    }
    /**
     * This function will get the user's profile information
     */
    public function index()
    {
        $profile = User::getUsersProfileData();
        return response()->json(['user' => $profile]);
    }

    /**
     * for updating the
     *
     * @param Request $request
     * @return void
     */
    public function store(ProfileRequest $request)
    {

        try {
            DB::beginTransaction();

            $address = $request->address;

            $workHistory = $request->workHistory;

            $profile = $request->profile;

            $addressResponse = $this->profile->updateProfileAddress($address);

            $workHistoryresponse = $this->profile->profileWorkHistory($workHistory);

            $profileData = $this->profile->updateProfileData($profile);

            DB::commit();

            if ($addressResponse) {
                return $this->successApiResponse(__('core.profileSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.profileError'));
            }
        } catch (\Throwable$th) {
            DB::rollback();
            return $this->errorApiResponse(__('core.profileError'));
        }

    }

    /**
     * upload profile image api
     *
     * @param ProfileImageRequest $request
     * @return void
     */
    public function uploadProfileImage(ProfileImageRequest $request)
    {
        try {
            $profileUpdate = $this->profile->uploadProfileImage($request->image);

            if ($profileUpdate) {
                return $this->successApiResponse(__('core.imageUploadSuccess'), $profileUpdate);
            } else {
                return $this->unprocessableApiResponse(__('core.imageUploadError'));
            }

        } catch (\Throwable$th) {
            return $this->errorApiResponse(__('core.imageUploadError'));
        }

    }
}
