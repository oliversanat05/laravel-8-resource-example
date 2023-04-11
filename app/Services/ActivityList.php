<?php
namespace App\Services;
use Auth;
use App\Models\EventTrack;
use App\Models\EventCoachTrack;

class ActivityList{
    /**
     * method is used to handle the getting data from the database
     */
    public function getUserLists($startDate, $endDate, $pageSize){
        // $getFields= "*, userId,userName, sum(callmaximizer) as callmaximizer,sum(dashboard) as dashboard,sum(startHere) as startHere,sum(coachPath) as coachPath,sum(vmap) as vmap,sum(login) as login,sum(trackData) as trackData";
        if(Auth::user()->role_id !== 4){
            $events = EventTrack::whereUserid(Auth::user()->user_id)
                        ->with('user')
                        ->whereBetween('transdate', [date("Y-m-d", strtotime($startDate)), date("Y-m-d", strtotime($endDate))])
                        // ->groupBy('userId')
                        ->paginate($pageSize);
        }else{
            $events = EventTrack::whereBetween('transdate', [date("Y-m-d", strtotime($startDate)), date("Y-m-d", strtotime($endDate))])
                        // ->groupBy('userId')
                        ->paginate($pageSize);
        }

        return $events;
    }

    /**
     * method is used to get the coach activity list from the database.
     */
    public function getCoachLists($startDate, $endDate, $pageSize){
        // $getFields= "userId, userName, coachUserId, coachUserName, sum(callmaximizer) as callmaximizer,sum(dashboard) as dashboard,sum(startHere) as startHere,sum(coachPath) as coachPath,sum(vmap) as vmap,sum(trackData) as trackData";
        if(Auth::user()->role_id == 4)
            $coachLists = EventCoachTrack::whereBetween('transdate', [date("Y-m-d", strtotime($startDate)), date("Y-m-d", strtotime($endDate))])
                            // ->groupBy('userId')
                            ->orderBy('coachUserName', 'ASC')
                            ->paginate($pageSize);

        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 3)
            $coachLists = EventCoachTrack::where('coachUserId', Auth::user()->user_id)
                            ->whereBetween('transdate', [date("Y-m-d", strtotime($startDate)), date("Y-m-d", strtotime($endDate))])
                            // ->groupBy('userId')
                            ->paginate($pageSize);

        else
            $coachLists = EventCoachTrack::where('coachUserId', Auth::user()->user_id)
                            ->whereBetween('transdate', [date("Y-m-d", strtotime($startDate)), date("Y-m-d", strtotime($endDate))])
                            // ->groupBy('userId')
                            ->paginate($pageSize);
        return $coachLists;
    }
}
