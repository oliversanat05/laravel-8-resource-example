<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use Config;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Profile\FilterProfile;
use App\Services\Dashboard\DashboardSystemService;
use App\Http\Requests\StoreDashboardProfileRequest;

class DashboardController extends Controller
{
    use ApiResponse;

    private $dashboardProfileDependency;

    public function __construct()
    {
        $this->dashboardProfileDependency = new DashboardSystemService();
    }

    /**
     * method used to store or create the dashboard profile
     *
     * @param StoreDashboardProfileRequest $request
     * @return response
     */
    public function store(StoreDashboardProfileRequest $request){
        try{
            $filterData = FilterProfile::createOrUpdateProfile($request);

            return $this->successApiResponse(__('core.profileSave'), $filterData);
        }catch(\Exception $e){
            return $e->getMessage();
            return $this->errorApiResponse(__('core.profileSaveError'));
        }
    }

    /**
     * method used to show the dashboard profile
     *
     * @param Request $request
     * @param int $id
     * @return json
     */
    public function show(Request $request, $id){
        try{
            $data = [];
            $profileName = FilterProfile::getFilterName($id);
            if($profileName){
                $profile = $this->dashboardProfileDependency->getVmapByFilterId($id);
                $data['name'] = $profileName;
                $data['profile'] = $profile;

                // array_merge($data['vmaps'], $data['values']);
                // dd($data['values']);

                return $this->successApiResponse(__('core.profile'), $data);
            }else{
                return $this->successApiResponse(__('core.profileNotFound'));
            }
        }catch(\Exception $e){
            dd($e);
            return $this->errorApiResponse(__('core.profileFetchError'));
        }
    }

    /**
     * method used to update the dashboard profile
     *
     * @param StoreDashboardProfileRequest $request
     * @param int $id
     * @return void
     */
    public function update(StoreDashboardProfileRequest $request, $id){
        try{
            $parentId = 0;
            $count = FilterProfile::getUniqueProfileFilters($request, $id);
            if($count)
                return $this->successApiResponse(__('core.profileAlreadyExist'));

            if(!FilterProfile::getVmapByFilterId($id))
                return $this->unprocessableApiResponse(__('core.profileNotFound'));

            $filterProfile = FilterProfile::createOrUpdateProfile($request, $id);

            return $this->successApiResponse(__('core.profileUpdate'), $filterProfile);
        }catch(\Exception $e){
            return $e->getMessage();
            return $this->errorApiResponse(__('core.profileUpdateError'));
        }
    }

    /**
     * method used to delete vmap for the profile
     *
     * @param int $id
     * @return response
     */
    public function deleteVmap($id){
        try{
            FilterProfile::deleteVmap($id);
            return $this->successApiResponse(__('core.profileVmapDelete'));
        }catch(\Exception $e){
            return $this->errorApiResponse(__('core.profileVmapDeleteError'));
        }
    }

    /**
     * method used to delete the profile
     *
     * @param int $id
     * @return response
     */
    public function destroy($id){
        try{
            if(FilterProfile::where('filter_id', $id)->exists()){
                FilterProfile::deleteProfile($id);
                return $this->successApiResponse(__('core.deleteProfile'));
            }else{
                return $this->unprocessableApiResponse(__('core.exists'));
            }

        }catch(\Exception $e){
            return $this->errorApiResponse(__('core.deleteProfileError'));
        }
    }

    /**
     * fetching all the dashboard profiles for the auth user
     *
     * @return void
     */
    public function index()
    {
        $filterProfileData = FilterProfile::whereUserId(Auth::user()->user_id)->whereFilterParentId('constants.defaultFilterProfile')->get();

        return response()->json([
            "status" => true,
            "data" => $filterProfileData
        ]);
    }
}
