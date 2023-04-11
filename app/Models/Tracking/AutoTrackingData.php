<?php

namespace App\Models\Tracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoTrackingData extends Model
{
    use HasFactory;

    protected $table = 'autoTrackingDates';
    public $primaryKey = 'id';

    protected $fillable = ['user_id', 'comment', 'data'];
}
