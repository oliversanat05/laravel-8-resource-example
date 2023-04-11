<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\EventTrack;
use App\Models\EventCoachTrack;
use App\Services\ActivityList;

class ActivityController extends Controller
{
    /**
     * constructor called
     */
    public function __construct(){
        // $this->activity = new ActivityList();
    }
    /**
     * This function is use to get user activity data
     * @param NA Id
     * @return object
     */
    public static function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $pageSize = $request->query('pageSize');
        try{
            $activity = new ActivityList;
            $events = $activity->getUserLists($startDate, $endDate, $pageSize);
            $coaches = $activity->getCoachLists($startDate, $endDate, $pageSize);
            $response = compact ('events', 'coaches');
            return response()->json(["data" => $response], 200);
        } catch(\Exception $ex){
            return response()->json($ex->getMessage(), 422);
        }
    }
}
