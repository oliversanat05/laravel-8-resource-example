<?php

namespace App\Models\Tracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Config;
use App\Models\Succession\Kpi;

class TrackingData extends Model
{
    use HasFactory;

    protected $table = 'trackingData';
    public $primaryKey = 'trackingDataId';

    protected $guarded = [];


    /**
     * This function is save the data of the tracking data to the database
     * @param $request
     * @return array
     *
     */
    public function saveTrackData($request)
    {
        return self::create([
            'startDate' => Carbon::parse($request['beginingDate'])->format(Config::get('constants.dbDateFormat')),
            'endDate' => Carbon::parse($request['endingDate'])->format(Config::get('constants.dbDateFormat')),
            'comment' => (string)($request['comment']),
            'trackingValue' => $request['data'],
            $request['activity'].'Id' => $request['activityId'],
        ]);
    }

    /**
     * This function will delete the tracking data
     */
    public static function deleteRecords($request)
    {
        return self::find($request)->delete();
    }

    /**
     * This will check if the tracking exists or not
     * in the database
     */
    public static function checkTrackingIdExists($request)
    {
        return self::whereTrackingdataid($request)->exists();
    }

    /**
     * method used to create relationship between two models
     */

    public function kpis(){
        return $this->hasOne(Kpi::class, 'kpiId', 'kpiId');
    }
}
