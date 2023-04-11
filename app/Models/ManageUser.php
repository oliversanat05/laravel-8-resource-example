<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManageUser extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vwUserManagement';

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

    protected $guarded =  array('userId');
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */


    /**
     * function to provide list of coaches
     * @return array
     */
    public static function getCoaches()
    {
        $users = ManageUser::whereDeletedAt(null)
        ->with('children')
        ->where('coachUserId', '>', 0)
        ->orderBy('firstName')
        ->groupBy('userId')
        ->get()
        ->toArray();

        return self::getUsers($users);
    }

    /**
     * Create array of users
     * @param $users
     * @return mixed
     */
    public static function getUsers($users)
    {
        $oldUserName = '';
        $usersArray = array();

        foreach ($users as $rows) {

            $userName = $rows['username'];

            $firstName = mb_convert_encoding($rows['firstName'], 'UTF-8', 'HTML-ENTITIES');
            $lastName = mb_convert_encoding($rows['lastName'], 'UTF-8', 'HTML-ENTITIES');

            $userIndex = $rows['userId'];
            $usersArray[$userIndex]['id'] = $rows['userId'];
            $usersArray[$userIndex]['userName'] = $rows['username'];
            $usersArray[$userIndex]['parent_id'] = $rows['coachUserId'];
            $usersArray[$userIndex]['name'] = $firstName . ' ' . $lastName;
            $usersArray[$userIndex]['first_name'] = $firstName;
            $usersArray[$userIndex]['last_name'] = $firstName;
            $usersArray[$userIndex]['roleId'] = $rows['userRoleId'];
            $usersArray[$userIndex]['userRole'] = $rows['userRole'];
            $usersArray[$userIndex]['email'] = $rows['email'];
            $usersArray[$userIndex]['avatar'] = '/pics/'.$rows['pic'];
            $usersArray[$userIndex]['lastLogin'] = $rows['lastLoginDate'];
            $usersArray[$userIndex]['hasClient'] = $rows['hasClient'];
            $usersArray[$userIndex]['active'] = $rows['active'];
            $usersArray[$userIndex]['orders'] = $rows['clientOrder'];

            if ($rows['userRoleId'] != 0 && $oldUserName != $userName)
                $usersArray[$rows['userRole']] = 1;
        }

        return $usersArray;
    }

    public function children() {
        return $this->hasMany(ManageUser::class, 'coachUserId', 'userId');
    }

}
