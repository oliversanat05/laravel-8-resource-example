<?php

namespace App\Models\Succession;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tracking\TrackingData;

class Kpi extends Model
{
    use HasFactory;

    protected $table = 'kpi';
    public $primaryKey = 'kpiId';

    protected $fillable = ['valueId', 'kpiName', 'assignDate', 'dueDate', 'completedDate', 'statusId', 'showOnDashboard', 'includeInReporting', 'includeInAvatar', 'trackSign', 'goal', 'isAccumulate', 'url', 'delegateTo', 'qualifierTo', 'daily', 'weekly', 'monthly', 'quarterly', 'annually', 'description', 'seasonalGoal', 'seasonalSign', 'successScale', 'tracking', 'includeInProfile'];

    /**
     * Get the activity name and their parents for level 2.
     */
    public function value()
    {
        return $this->belongsTo('App\Models\Succession\Value', 'valueId', 'valueId');
    }

    /**
     * Create a new model instance to use the polymorphic relation with KPI to Strategy.
     * @param NA
     * @return void
     */
    public function strategy()
    {
        return $this->hasMany('App\Models\Succession\Strategy', 'kpiId', 'kpiId');
    }

    /**
     * method used to create relationship between two models
     */
    public function trackingData(){
        return $this->hasMany(TrackingData::class, 'kpiId', 'kpiId');
    }
}
