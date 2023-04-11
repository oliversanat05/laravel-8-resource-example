<?php

namespace App\Http\Controllers\Api\V1\CoachPath;

use App\Http\Controllers\Controller;
use App\Services\ActionItemService\ActionItemService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use DB;

class ActionItemController extends Controller
{
    use ApiResponse;

    private $actionItem;

    public function __construct()
    {
        $this->actionItem = new ActionItemService();
    }

    /**
     * for fetching the action item data
     *
     * @return void
     */
    public function index()
    {

        $response = $this->actionItem->getActionItemData();

        return $this->successApiResponseWithoutMessage($response);
    }

    /**
     * for saving the action item checklist data
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $params = $request->all();
            $response = $this->actionItem->storeActionItemData($params);
            DB::commit();

            if ($response) {
                return $this->successApiResponse(__('core.actionItemSaveSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.actionItemSaveError'));
            }
        } catch (\Throwable$th) {

            DB::rollback();
            return $this->errorApiResponse($th->getMessage());
        }

    }
}
