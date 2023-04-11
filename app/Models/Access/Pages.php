<?php

namespace App\Models\Access;

use Config;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pages extends Model {
	use HasFactory;

	protected $table = 'pages';
	public $primaryKey = 'ID';
    protected $hidden = ['pivot'];

	/**
	 * This route will get the pages
	 * according the status
	 */
	public static function pages() {
		return self::where('status', true)
			->where('parentPageId', Config::get('statistics.parentPageId'))
			->get();
	}

    /**
     * The roles that belong to the Pages
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(UserRole::class, 'rolePermission', 'pagesId', 'userRoleId');
    }
}
