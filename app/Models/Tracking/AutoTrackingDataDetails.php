<?php

namespace App\Models\Tracking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoTrackingDataDetails extends Model
{
    use HasFactory;

    protected $table = 'autoTrackingActivityDetails';
    public $primaryKey = 'id';

    protected $fillable = ['user_id', 'activity_data'];
}
