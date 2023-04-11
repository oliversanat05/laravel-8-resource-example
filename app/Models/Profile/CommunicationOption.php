<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Carbon\Carbon;
use Config;

class CommunicationOption extends Model
{
    use HasFactory;

    public $primaryKey = 'COID';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'communicationOption';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * This function will save the communication details for the delegate
     */
    public static function createCommunication($request, $id)
    {
        return self::create([
            'userId' => Auth::user()->user_id,
            'eID' => $id,
            'frequency' => $request['frequency'],
            'cPreference' => $request['preferedCommunication'],
            'preferenceType' => $request['preferenceType'],
            'email' => $request['email'],
            'cell' => $request['cell'],
            'dayTime' => $request['dayTime'],
            'dueDate' => $request['dueDate'],
            'dateRange' => !is_null($request['dateRange']) ? $request['dateRange'] : 0,
            'startDate' => Carbon::parse($request['startDate'])->format(Config::get('constants.dbDateFormat')),
            'endDate' => Carbon::parse($request['endDate'])->format(Config::get('constants.dbDateFormat'))
        ]);
    }

     /**
     * This function will send notifications to users
     * @param NA
     * @return object
     */
    public function getAlertData($delegateName)
    {
        date_default_timezone_set('US/Eastern');

        //check delegate user's communicatiion preference
        $checkDelegateCommunicationPreference = self::where('eID', $delegateName)->where('userId', Auth::user()->user_id)->first();

        // dd($checkDelegateCommunicationPreference);
        if ($checkDelegateCommunicationPreference->cPreference === Config::get('statistics.cPreference')) {
            return false;
        } else {
            return self::where('eID', $delegateName)
            ->where('userId', Auth::user()->user_id)
            ->whereIn('preferenceType', Config::get('statistics.preferenceType'))
            ->where('frequency', '!=', Config::get('statistics.frequency'))
            ->get()->toArray();
        }
    }
}
