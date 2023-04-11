<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallTime extends Model
{
    protected $table = "callTime";
    public $primaryKey = 'callTimeId';
    public $timestamps = false;

}
