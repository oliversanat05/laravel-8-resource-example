<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCoach extends Model
{
    protected $table = "clientCoach";
    public $primaryKey = 'clientCoachId';
    public $timestamps = false;

    protected $fillable = [
      'clientUserId', 'coachUserId', 'additionalCoach'
    ];

    /**
     * Get the user detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
      return $this->belongsTo('App\Models\User', 'clientUserId', 'user_id');
    }

     /**
     * Get the role detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function children()
    {
        return $this->hasMany('App\Models\User', 'user_id', 'clientUserId');
    }

    /**
	 * method used to get all users with filter
	 */
	public static function usersByParent($params, $pageSize)
	{
  		return ClientCoach::with(['children'])->leftJoin('users', 'user_id', 'clientUserId')->where('coachUserId', $params['parent'])
  			->when($params['title'] != null,
  			function($query) use($params){
  				return $query->where('name', 'LIKE', '%'. $params['title'] .'%');
  			})
  			->when($params['role_id'] > 0,
  			function($query) use($params){
  				return $query->where('role_id', $params['role_id']);
  			})
  			->when($params['status'] != null,
  			function($query) use($params){
  				return $query->where('status', $params['status']);
  			})
  		->paginate($pageSize);
   }
}
