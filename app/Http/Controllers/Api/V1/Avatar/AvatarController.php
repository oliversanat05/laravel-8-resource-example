<?php

namespace App\Http\Controllers\Api\V1\Avatar;

use DB;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Avatar\AvatarService;

class AvatarController extends Controller
{

    use ApiResponse;

    private $avatar;

    public function __construct()
    {
        $this->avatar = new AvatarService();
    }

    /**
     * for fetching the avatar details for the logged in user
     *
     * @return void
     */
    public function index()
    {
        try {
            $response = $this->avatar->getAvatarDetails();

            return $this->successApiResponseWithoutMessage($response);
        } catch (\Throwable$th) {
            return $this->errorApiResponse($th->getMessage());
        }
    }

    /**
     * for saving the avatar details
     *
     * @param Request $request
     * @return void
     */
    public function saveAvatarData(Request $request)
    {
        try {

            DB::beginTransaction();
            $params = $request->all();

            $response = $this->avatar->saveOrUpdateAvatarData($params);

            DB::commit();

            return $this->successApiResponse(__('core.saveAvatarSuccess'));
        } catch (\Throwable$th) {

            DB::rollback();
            return $this->errorApiResponse($th->getMessage());
        }
    }

    /**
     * to get the parameter for
     *
     * @param [type] $queryParams
     * @return void
     */
    public function getAvatar(Request $request)
    {
        $queryParams = $request->query();

        return response()->view('avatar.index', compact('queryParams'));

    }
}
