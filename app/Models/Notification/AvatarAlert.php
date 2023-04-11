<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvatarAlert extends Model
{
    use HasFactory;

    protected $table = 'avatarAlert';
    public $primaryKey = 'AID';
}
