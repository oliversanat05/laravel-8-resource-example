<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Services\NotificationService\NotificationService;
use App\Traits\ApiResponse;
use DB;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    private $notification;

    public function __construct()
    {
        $this->notification = new NotificationService();
    }

    /**
     * this function will get the notification link
     * according to the logged in user
     * @param Request $request
     * @return json
     */
    public function getNotification(Request $request)
    {

        try {

            DB::beginTransaction();

            $notification = $this->notification->handle($request->all());

            DB::commit();

            if (!is_null($notification)) {
                return $notification;
            } else {
                return $this->unprocessableApiResponse(__('core.emptyResponse'));
            }
        } catch (\Throwable$th) {

            // dd($th);
            DB::rollback();
            return $this->errorApiResponse($th->getMessage());
        }

    }
}
