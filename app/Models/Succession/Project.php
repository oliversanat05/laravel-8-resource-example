<?php

namespace App\Models\Succession;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'project';
    public $primaryKey = 'projectId';
    public $timestamps = true;

    protected $fillable = ['projectName', 'dueDate', 'assignDate', 'includeInAvatar', 'includeInProfile', 'goal', 'successScale', 'trackSign', 'seasonalSign', 'seasonalGoal', 'description', 'strategyId', 'daily', 'weekly', 'monthly', 'annually', 'delegateTo', 'qualifierTo', 'completedDate', 'url', 'showOnDashboard', 'includeInReporting'];

    public function criticalActivity()
    {
        return $this->hasMany('App\Models\Succession\CriticalActivity', 'projectId', 'projectId');
    }

    /**
     * Get the activity name and their parents for level 4.
     */
    public function strategy()
    {
        return $this->belongsTo('App\Models\Succession\Strategy', 'strategyId', 'strategyId');
    }
}
