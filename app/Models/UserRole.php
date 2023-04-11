<?php

namespace App\Models;

use App\Models\Access\Pages;
use Illuminate\Support\Facades\Auth;
use App\Models\Access\RolePermission;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model {
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'userRole';
    protected $primaryKey = 'UserRoleId';
    protected $hidden = ['pivot'];

	/**
	 * Removing dynamic update_at column
	 *
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

	/**
	 * [$guarded description]
	 * @var array
	 */

	protected $guarded = array('userRoleId');

	/**
	 * This function will query the roles from the table
	 */
	public static function getUserRoles() {
		return self::select('userRoleId as role_id', 'description as label')
			->whereCanlogin(true)
			->orderBy('sortOrder')
			->get();
	}

	/**
	 * relation between role and user
	 */
	public function users(){
		return $this->hasMany(User::class, 'role_id', 'userRoleId');
	}

    public function permission()
    {
        return $this->hasMany(RolePermission::class, 'userRoleId', 'userRoleId');
    }
}
