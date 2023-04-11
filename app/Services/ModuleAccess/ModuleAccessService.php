<?php

namespace App\Services\ModuleAccess;

use App\Models\modules\ModuleAccess;
use App\Models\User;
use Config;

class ModuleAccessService
{

    /**
     * get all the users that belongs to wow
     *
     * @return void
     */
    public function getWowAccess($pageSize)
    {
        return User::with(['modules', 'user_role:userRoleId,description'])->where('status', 1)
            ->whereNotIn('role_id', [Config::get('statistics.delegateUserType'), Config::get('statistics.encqualifierType')])
            ->paginate($pageSize, ['user_id', 'name', 'user_name', 'email', 'role_id']);
    }

    /**
     * update the access for the wow
     *
     * @return void
     */
    public function grantWowAccess($modules)
    {
        $permissions = [];
        foreach ($modules as $key => $module) {

            if ($module['is_assign'] == true) {
                if (!ModuleAccess::where('moduleId', $module['module_id'])->whereIn('userId', [$module['user_id']])->exists()) {

                    array_push($permissions, [
                        'moduleId' => $module['module_id'],
                        'userId' => $module['user_id'],
                        'is_assign' => true,
                    ]);
                }
            } else {
                ModuleAccess::where('moduleId', $module['module_id'])->whereIn('userId', [$module['user_id']])->delete();
            }
        }

        return ModuleAccess::insert($permissions);
    }
}
