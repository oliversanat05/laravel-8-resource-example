<?php

namespace App\Models\CoachPath;

use App\Models\CoachPath\SurveyContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyContentArea extends Model
{
    use HasFactory;

    protected $table = 'surveyContentArea';

    /**
     * Get all of the surveyContent for the SurveyContentArea
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyContent()
    {
        return $this->hasMany(SurveyContent::class, 'surveyContentAreaId', 'surveyContentAreaId');
    }
}
