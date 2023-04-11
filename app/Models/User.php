<?php

namespace App\Models;

use Auth;
use App\Models\User;
use App\Models\UserRole;
use App\Models\ENCQualifier;
use App\Models\modules\Module;
use App\Models\Profile\Profile;
use Laravel\Passport\HasApiTokens;
use App\Models\Initial\ClientCoach;
use App\Models\Profile\FilterProfile;
use App\Models\Tracking\DelegateUser;
use Illuminate\Support\Facades\Config;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

	protected $table = 'users';
	public $primaryKey = 'user_id';
	public $timestamps = true;
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var string[]
	 */
	protected $fillable = [
		'name', 'email', 'password', 'user_name', 'status', 'dialNumber', 'accessCode', 'meetingLink', 'role_id', 'lastLoginDate'
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
		'pivot'
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	/**
	 * method used to assign token
	 */
	public function token() {
		return $this->hasOne(OauthAccessToken::class,'user_id', 'id');
	}

	/**
	 * Get the profile detail.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function profile() {
		return $this->belongsTo(Profile::class, 'user_id', 'userId')->with('address', 'workHistory');
	}

	/**
	 * Get all of the delegates for the User
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function delegates() {
		return $this->hasMany(DelegateUser::class, 'userId', 'user_id');
	}

	/**
	 * Get all of the qualifier for the User
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function qualifier() {
		return $this->hasMany(EncQualifier::class, 'user_id', 'userName');
	}

	/**
	 * relation between user and the role
	 */
	public function role() {
		return $this->hasOne(UserRole::class, 'userRoleId', 'role_id');
	}

    /**
     * this function will count the no. of users as per role
     */
	public static function countRoleUser() {
        return UserRole::withCount('users')->where('canLogin', true)->get();
	}

	/**
	 * method used to get all users
	 */
	public static function allUsers($pageSize){
		return User::paginate($pageSize);
	}

	/**
	 * method used to get all users with filter
	 */
	public static function usersByFilters($params, $pageSize){
			return User::with(['children.user'])->where(function ($query) use($params){
				$query->when($params['email'] != null,
				function($query) use($params){
					return $query->where('email', $params['email']);
				})
				->when($params['title'] != null,
				function($query) use($params){
					return $query->where('name', 'LIKE', '%'. $params['title'] .'%');
				})
				->when(($params['role_id'] == null),
				function ($query) use($params){
					return $query->whereNotNull('role_id');
				},
				function ($query) use($params){
					return $query->where('role_id', $params['role_id']);
				})
				->when(!empty($params['status']),
                function ($query) use($params){
                    return $query->where('status', $params['status']);
                },
				function($query) use($params) {
                    return $query->where('status', $params['status']);
                })
				->when($params['parent'] == 0,
				function ($query) use($params){
					return $query;
				},
				function ($query) use($params){
					$query->leftJoin('clientCoach', 'coachUserId', 'user_id');
					return $query->where('user_id', $params['parent']);
				});
            })
				->paginate($pageSize);
	}

    /**
     * this function will get the user's profile information
     */
    public static function getUsersProfileData()
    {
        return $profile = self::with(['profile', 'role.permission.pages'])->where('user_id', Auth::user()->user_id)->get();
    }

    /**
     * Get all of the filterProfile for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filterProfile()
    {
        return $this->hasMany(FilterProfile::class, 'user_id', 'user_id');
    }
	/**
	 * method used to count the users in the database by role
	 */
	public static function userCountByRole($roleId){
		return UserRole::where('userRoleId', $roleId)->withCount('users')->first();
	}

	/**
	 * method used to get the user details.
	 */
	public static function userDetails($id){
		return User::where('user_id', $id)->first();
	}

    /**
     * Get the coach that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coach()
    {
        return $this->belongsTo(ClientCoach::class, 'user_id', 'clientUserId');
    }

    /**
     * This function will create the delegate users
     * @param
     */
    public static function delegateUsers($name)
    {
        return self::create([
            'name' => $name,
            'role_id' => Config::get('statistics.delegateUserType'),
            'user_name' => $name. '_'.time(),
            'email' => '',
            'password' => true,
            'status' => '',
            'dialNumber' => '',
            'accessToken' => '',
            'meetingLink' => '',
            'isCompleted' => '',
            'remember_token' => ''
        ]);
    }
	 /**
     * Get the role detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function children()
    {
        return $this->hasMany('App\Models\ClientCoach', 'coachUserId', 'user_id');
    }

	/**
     * Get the role detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user_children()
    {
        return $this->hasMany('App\Models\ClientCoach', 'coachUserId', 'user_id');
    }

	/**
	 * method used to get all users
	 */
	public static function allSuperUsers($pageSize){
		return User::whereRoleId(config('constants.adminRoleId'))->paginate($pageSize);
	}
    /**
     * Get the wow that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'moduleAccess', 'userId', 'moduleId');
    }

    /**
     * Get the user_role that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_role()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'userRoleId');
    }
}
