<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Profile extends Model
{
    use HasFactory;

    public $primaryKey = 'profileId';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'profile';

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
    protected $fillable = [
      'userId', 'timeZoneId', 'firstname', 'lastname', 'email', 'rangeStartDate', 'rangeEndDate', 'workingDaysWeekly'
    ];

    /**
     * [$guarded description]
     * @var array
     */

    protected $guarded = array('profile_id');

    /**
     * Get the address that owns the Profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->hasMany(Address::class, 'profileId', 'profileId');
    }

    /**
     * Get the workHistory associated with the Profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workHistory()
    {
        return $this->hasMany(WorkHistory::class, 'profileId', 'profileId');
    }

    /**
     * This function will save the delegates profile after creating the delegate
     * @param $name, $userId
     * @return array
     */
    public static function saveDelegateProfile($name, $userId)
    {
        return self::create([
            'userId' => $userId,
            'firstName' => $name,
        ]);
    }

    /**
     * this function will update the phone and name of the delegate's profile
     */
    public static function updateDelegateProfile($userId, $cell, $name)
    {
        $profileData = Profile::where('userId', $userId);

        if($profileData->count()){
            $profile = $profileData->first();

            $profile->prefPhone = $cell;
            $profile->firstName = $name;
            $profile->lastName = '';
            $profile->save();
        }
    }

    /**
     * This function will get start and end date range
     * @param $userId
     * @return object
     */
    public function getUserSetDateRange($userId)
    {
        $result = self::where('userId', $userId)->first()->toArray();
        return $result;
    }
}
