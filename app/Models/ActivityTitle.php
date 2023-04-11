<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityTitle extends Model {
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'activityTitle';
	public $primaryKey = 'ID';
	protected $guarded = ['_token'];

	protected $fillable = ['vmvId', 'valueTitle', 'kpiTitle', 'strategyTitle', 'projectTitle', 'caTitle', 'kActivityCheck', 'sActivityCheck', 'pActivityCheck', 'cActivityCheck'];

	/**
	 * this function will add and udpate the activity title for the
	 * selected vmap
	 *
	 * @param $request
	 * @return array
	 */
	public function getActivtyData($request, $id) {
		return self::updateOrCreate(
			[
				'vmvid' => $id,
			],
			[
				'valueTitle' => $request['valueTitle'],
				'kpiTitle' => $request['kpiTitle'],
				'strategyTitle' => $request['strategyTitle'],
				'projectTitle' => $request['projectTitle'],
				'caTitle' => $request['criticalActivityTitle'],
				'kActivityCheck' => $request['kActivityCheck'],
				'sActivityCheck' => $request['sActivityCheck'],
				'pActivityCheck' => $request['pActivityCheck'],
				'cActivityCheck' => $request['cActivityCheck'],
			]);

	}
}
