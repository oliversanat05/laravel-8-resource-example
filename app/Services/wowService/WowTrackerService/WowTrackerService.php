<?php

namespace App\Services\wowService\WowTrackerService;

use App\Models\wowModels\Client;
use App\Models\wowModels\WowTracker;
use App\Models\wowModels\Tier;
use App\Models\wowModels\WowTrackerHeading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WowTrackerService
{

    /**
     * This function create default tiers
     * for all global users`
     *
     * @param  $data Contains TierValues
     * @return \Illuminate\Http\Response
     */

    /**
     * Store wow tracker Data in database
     *
     * @return \object
     */
    public function storeOrUpdateWowTrackerData($data, $id)
    {
        if (isset($data['wow_tracker'])) {
            foreach ($data['wow_tracker'] as $key => $value) {
                $data['wow_tracker'][$key]['listing_id'] = (int) $id;
            }
            WowTracker::where('listing_id',(int) $id)->delete();
            $saveData = WowTracker::upsert($data['wow_tracker'], ['unique_tracker'], ['idea_id']);
        }
        if (isset($data['clients'])) {
            $saveData = Client::upsert($data['clients'], ['id'], ['address', 'birthday', 'aniversery']);
        }
        if ($saveData) return true;
        return null;
    }

    /**
     * Get Tracker Headings
     *
     * @return \object
     */
    public function trackerHeadings()
    {
        $headings = WowTrackerHeading::get(['id', 'month']);
        return $headings;
    }

    /**
     * Get wow tracker data as per client
     *
     * @return \object
     */
    public function wowTrackerData($id)
    {
        $data = Client::with(['wow_tracker','clientScore:client_id,id,score'])
        ->where('listing_id', $id)->orderBy('id','DESC')->get(['id', 'name', 'birthday', 'aniversery', 'address'])->toArray();

        $tiers = Tier::where('user_id',Auth::user()->user_id)->where('status',1)->get(['min_value','max_value','id','tier_type'])->toArray();
        if(count($tiers)){}
        else{
            $tiers = Tier::where('user_id',null)->get()->toArray(['min_value','max_value','id','tier_type']);
        }
        $formatted = formatClientGapData($data,$tiers);
        return $formatted;
    }
}
