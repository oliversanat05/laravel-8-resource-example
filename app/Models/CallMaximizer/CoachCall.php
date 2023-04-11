<?php

namespace App\Models\CallMaximizer;

use App\Models\Initial\ClientCoach;
use Auth;
use Carbon\Carbon;
use Config;
use Illuminate\Database\Eloquent\Model;

class CoachCall extends Model {
	protected $table = "coachCall";
	public $primaryKey = 'coachCallId';

    protected  $guarded = [];

	/**
	 * relation between coachCall and call maximizer
	 */
	public function overdueList(){
		return $this->hasMany('App\Models\CallMaximizer\CallMaximizerData', 'coachCallId', 'coachCallId')
                            ->whereNotNull('longComment')
                            ->where('updated', false)
                            ->where('callMaximizerId','>=',Config::get('constants.assignment'))->orderBy('callMaximizerDataId', 'ASC');
	}

	/**
	 * relation between coach call and call maximizer
	 */
	public function callMaximizerData() {
		return $this->hasMany('App\Models\CallMaximizer\CallMaximizerData', 'coachCallId', 'coachCallId');
	}

	/**
	 * relation between coach call and timezone
	 */
	public function timezone() {
		return $this->hasOne('App\Models\Timezone', 'timeZoneId', 'timeZoneId');
	}

	/**
	 * relation between coach call and next timezone
	 */
	public function nextTimezone() {
		return $this->hasOne('App\Models\Timezone', 'timeZoneId', 'nextTimeZoneId');
	}

	/**
	 * relation between coach call and timezone
	 */
	public function callTime() {
		return $this->hasOne('App\Models\CallTime', 'callTimeId', 'callTimeId');
	}

	/**
	 * relation between coach call and next timezone
	 */
	public function nextCallTime() {
		return $this->hasOne('App\Models\CallTime', 'callTimeId', 'nextCallTimeId');
	}

	/**
	 * This function will get the callmaximier data
	 * @param $request
	 * @return collection
	 */
	public static function getCallMaximizerDate($request) {

		$date = Carbon::parse($request)->format(Config::get('constants.dbDateFormat'));
		return self::where("clientUserId", Auth::user()->user_id)
			->whereCalldate($date)->get();
	}

	/**
	 * function for checking if coachcallid exists or not
	 * @param $request
	 */
	public static function checkCallExists($request) {
		return self::where('coachCallId', $request)->exists();
	}

    /**
     * helper function to create a coachCall
     */
	public static function addCoachCall($data) {
		$clientCoach = ClientCoach::whereClientuserid(Auth::user()->user_id)->first();

        $previousCallMaximizerDate = self::where('clientUserId', Auth::user()->user_id)->orderBy('coachCallId', 'DESC')->first();

		if ($clientCoach) {
			 return self::create([
				'clientUserId' => Auth::user()->user_id,
				'coachUserId' => $clientCoach->coachUserId,
				'callDate' => Carbon::parse($data['newCallMax'])->format(Config::get('constants.dbDateFormat')),
				'nextCallDate' => Carbon::parse($data['newCallMax'])->format(Config::get('constants.dbDateFormat')),
				'timeZoneId' => ($previousCallMaximizerDate) ? $previousCallMaximizerDate['timeZoneId'] : 1,
				'callTimeId' => ($previousCallMaximizerDate) ? $previousCallMaximizerDate['callTimeId'] : 1,
				'nextTimeZoneId' => ($previousCallMaximizerDate) ? $previousCallMaximizerDate['nextTimeZoneId'] : 1,
				'nextCallTimeId' => ($previousCallMaximizerDate) ? $previousCallMaximizerDate['nextCallTimeId'] : 1,
			]);
		}

	}
}
