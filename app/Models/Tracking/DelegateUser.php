<?php

namespace App\Models\Tracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Profile\CommunicationOption;
use Auth;

class DelegateUser extends Model
{
    use HasFactory;

    protected $table = 'delegateUsers';
    public $primaryKey = 'delegateUsersId';

    protected $guarded = [];

    /**
     * Removing dynamic update_at column
     *
     */
    public $timestamps = false;

    /**
     * Get the user detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class, 'userId', 'user_id');
    }

    /**
     * Get the user detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function communication() {

        // dd(Auth::user()->user_id);
        return $this->hasOne(CommunicationOption::class, 'eID', 'userId');
    }

    /**
     * This function will save the delegate details
     * @param $userId, $parentId, $isDelegate
     * @return array
     */
    public static function createDelegate($userId, $parentId, $isDelegate)
    {
        return self::create([
            'userId' => $userId,
            'parentId' => $parentId,
            'isDelegate' => $isDelegate
        ]);
    }
}
