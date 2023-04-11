<?php

namespace App\Models\Succession;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriticalActivity extends Model
{
    use HasFactory;

    protected $table = 'criticalActivity';
    public $primaryKey = 'criticalActivityId';

    protected $fillable = ['criticalActivityName', 'dueDate', 'assignDate', 'includeInAvatar', 'includeInProfile', 'goal', 'successScale', 'trackSign', 'seasonalSign', 'seasonalGoal', 'description', 'projectId', 'daily', 'weekly', 'monthly', 'annually', 'delegateTo', 'qualifierTo', 'completedDate', 'url', 'showOnDashboard', 'includeInReporting'];

    /**
     * Get the activity name and their parents for level 5.
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Succession\Project', 'projectId', 'projectId');
    }
}
