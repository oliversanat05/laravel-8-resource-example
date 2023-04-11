<?php

namespace App\Models\Succession;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    use HasFactory;

    protected $table = 'strategy';
    public $primaryKey = 'strategyId';

    protected $fillable = ['strategyName', 'dueDate', 'assignDate', 'includeInAvatar', 'includeInProfile', 'goal', 'successScale', 'trackSign', 'seasonalSign', 'seasonalGoal', 'description', 'kpiId', 'daily', 'weekly', 'monthly', 'annually', 'delegateTo', 'qualifierTo', 'completedDate', 'url', 'showOnDashboard', 'includeInReporting' ];

    /**
     * Get the activity name and their parents for level 3.
     */
    public function kpi()
    {
        return $this->belongsTo('App\Models\Succession\Kpi', 'kpiId', 'kpiId');
    }

    /**
     * Create a new model instance to use the polymorphic relation with Strategy project.
     * @param NA
     * @return void
     */
    public function project()
    {
        return $this->hasMany('App\Models\Succession\Project', 'strategyId', 'strategyId');
    }
}
