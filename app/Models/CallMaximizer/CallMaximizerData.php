<?php

namespace App\Models\CallMaximizer;

use App\Models\CallMaximizer\CoachCall;
use Illuminate\Database\Eloquent\Model;

class CallMaximizerData extends Model {
	protected $table = "callMaximizerData";
	public $primaryKey = 'callMaximizerDataId';

	protected $guarded = [];

	/**
	 * relation between callMaximizerData and callMaximizer
	 */
	public function callMaximizer() {
		return $this->hasOne('App\Models\CallMaximizer\CallMaximizer', 'callMaximizerId', 'callMaximizerId')->with('controlType');
	}

	/**
	 * this function will update the overdue activities
	 * @param $request
	 * @return JSON
	 */
	public static function updateOverDueActivity($request) {
		return self::whereCallmaximizerdataid($request)->update(['updated' => true]);
	}

	/**
	 * helper function for updating the callmaximizer
	 */
	public static function updateCallMaximizer($coachCallId, $callMaximizerId, $longComment, $satisfactionLevel, $motivationLevel, $updated) {

        // dd($longComment ? $longComment : $data['longComment']);
		return self::where('coachCallId', $coachCallId)
			->where('callMaximizerId', $callMaximizerId)
			->update([
				'longComment' => $longComment,
				'satisfactionLevel' => $satisfactionLevel,
				'motivationLevel' => $motivationLevel,
				'updated' => $updated,
			]);
	}

	/**
	 * Helper to create new callmaximizer assignment component
	 * @param $coachCallId, $callMaximzierId, $coachCallId
	 */
	public static function createCallMax($comment, $callMaximizerId, $coachCallId) {

		return self::create([
			'callMaximizerId' => $callMaximizerId,
			'coachCallId' => $coachCallId,
			'longComment' => $comment,
			'updated' => 0,
			'satisfactionLevel' => 0,
			'motivationLevel' => 0,
		]);
	}

    /**
     * Get all of the coachCall for the CallMaximizerData
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coachCall()
    {
        return $this->hasMany(CoachCall::class, 'coachCallId', 'coachCallId');
    }
}
