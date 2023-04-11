<?php

namespace App\Models\Access;

use App\Models\Access\Pages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'rolePermission';
    public $primaryKey = 'rolePermissionId';

    protected $fillable = [
        'is_assign', 'userRoleId', 'pagesId',
    ];

    public $timestamps = false;

    /**
     * method used to delete or create the role
     */

    public static function createOrDelete($request)
    {
        $permissions = [];

        foreach ($request as $key => $pageData) {

            if ($pageData['is_assign'] == true) {
                if (!self::whereIn('pagesId', [$pageData['page_id']])->whereIn('userRoleId', [$pageData['role_id']])->exists()) {

                    array_push($permissions, [
                        'pagesId' => $pageData['page_id'],
                        'userRoleId' => $pageData['role_id'],
                        'is_assign' => true,
                    ]);
                }
            } else {
                self::whereIn('userRoleId', [$pageData['role_id']])->whereIn('pagesId', [$pageData['page_id']])->delete();
            }
        }

        return $savePermissions = self::insert($permissions);
    }

    /**
     * Get the pages that owns the RolePermission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pages()
    {
        return $this->belongsTo(Pages::class, 'pagesId', 'ID')->whereStatus(true);
    }

}
