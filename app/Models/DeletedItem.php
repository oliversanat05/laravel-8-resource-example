<?php

namespace App\Models;

use Auth;
use Config;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedItem extends Model {
	use HasFactory;

	protected $table = 'deletedItem';
	public $primaryKey = 'itemId';

    protected $fillable = ['status', 'userId', 'tableName', 'tableId', 'name'];

	/**
	 * This function will set the deleted item to
	 * the table
	 */
	public static function deletedItem($request, $id, $type) {

		return self::create([
			'status' => Config::get('constants.deletedItem'),
			'userId' => Auth::user()->user_id,
			'tableName' => $type,
			'tableId' => $id,
			'name' => $request,
		]);
	}
}
