<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class EventTrack extends Model {
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'eventTrack';
	public $primaryKey = 'eventId';
	protected $guarded = ['_token'];

	/**
	 * relations between the event track and the user
	 */

	public function user() {
		return $this->hasOne(User::class, 'user_id', 'userId')->with('role');
	}

	public static function getClientActivity($startDate, $endDate, $pageSize) {
		return self::selectRaw('eventId, userId, userName, sum(login) as login, sum(vmap) as vmap, sum(callmaximizer) as callmaximizer, sum(dashboard) as dashboard, sum(trackData) as trackingData,  sum(coachPath) as coachPath, transDate, startHere')->whereBetween('transDate', [$startDate, $endDate])
            ->with(['user'])
			->groupBy('userId')->paginate($pageSize);
	}
}
