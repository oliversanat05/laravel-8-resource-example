<?php

namespace App\Models\modules;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $table = 'moduleName';

    protected $hidden = ['pivot'];

    /**
     * The user that belong to the Module
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'moduleAccess', 'moduleId', 'userId');
    }
}
