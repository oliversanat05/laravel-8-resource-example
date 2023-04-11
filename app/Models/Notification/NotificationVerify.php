<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationVerify extends Model
{
    use HasFactory;

    protected $table = 'notificationVerify';
    public $primaryKey = 'id';
}
