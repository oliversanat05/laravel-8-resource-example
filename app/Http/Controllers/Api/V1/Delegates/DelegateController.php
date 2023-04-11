<?php

namespace App\Http\Controllers\Api\V1\Delegates;

use DB;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tracking\DelegateUser;
use App\Http\Resources\Delegate\DelegateCollection;
use App\Http\Requests\delegateRequest\AddDelegateRequest;
use App\Services\DelegateUserService\DelegateUserService;
use App\Http\Requests\delegateRequest\AssignDelegateRequest;
use App\Http\Requests\delegateRequest\UpdateDelegateRequest;
use App\Http\Requests\delegateRequest\DelegateSuggestionRequest;

class DelegateController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->delegate = new DelegateUserService();
    }

    /**
     * This function will get the delegate list
     */
    public function index(Request $request)
    {

        $pageSize = $request->query('pageSize');

        $delegateList = DelegateUser::where('parentId', Auth::user()->user_id)
            ->with(['user', 'communication' => function ($query) {
                $query->where('userId', Auth::user()->user_id);
            }])
            ->whereNotNull('userId')
            ->paginate($pageSize);


        return new DelegateCollection($delegateList);

    }

    /**
     * this function will update the delegate user's data;
     * @param Request $request
     * @return JSON
     */
    public function update(UpdateDelegateRequest $request, $id)
    {
        try {
            return $this->delegate->updateDelegate($request->all(), $id);
        } catch (\Throwable $th) {
            dd($th);
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    /**
     * This function will delete the delegate and its communication
     * @param Request $request
     * @return JSON
     */
    public function destroy($id)
    {

        try {
            $deleteDelegate = $this->delegate->deleteDelegateData($id);

            if ($deleteDelegate) {
                return $this->successApiResponse(__('core.delegateDeletedSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.delegateDeleteError'));
            }
        } catch (\Throwable $th) {
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    public function communication($delegateId)
    {
        try {

            if (!empty($delegateId)) {
                return $communicationResponse = $this->delegate->getCommunicationOptionsForDelegates(Auth::user()->user_id, $delegateId);
            }

        } catch (\Throwable $th) {
            return $this->errorApiResponse(__('core.internalServerError'));
        }

    }

    /**
     * This function will add a new delegate to the system
     * @param AddDelegateRequest $request
     * @return JSON
     */

    public function store(AddDelegateRequest $request)
    {
        try {

            $saveDelegate = $this->delegate->addUserWithDelegate($request['name']);

            if ($saveDelegate) {
                return $this->successApiResponse(__('core.delegateSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.delegateExists'));
            }
        } catch (\Throwable $th) {

            DB::rollback();
            throw $th;
        }
    }

    /**
	 * for assigning the delegates
	 *
	 * @param Request $request
	 * @return void
	 */
	public function assignDelegates(AssignDelegateRequest $request)
	{
        try {
            $userId = $request->userId;
            $response = $this->delegate->assignDelegateToUser($userId, Auth::user()->user_id, true);

            if($response) {
                return $this->successApiResponse(__('core.delegateAssignSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.delegateAssignError'));
            }
        } catch (\Throwable $th) {
            return $this->errorApiResponse($th->getMessage());
        }


	}

    /**
     * get auto suggested delegates
     *
     * @param Request $request
     * @return void
     */
    public function getAutoSuggestedDelegates(DelegateSuggestionRequest $request)
    {
        $name = $request->query('name');
        $delegate = $this->delegate->getDelegates($name);

        return $this->successApiResponseWithoutMessage($delegate);
    }
}
