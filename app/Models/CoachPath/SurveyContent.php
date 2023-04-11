<?php

namespace App\Models\CoachPath;

use App\Models\CoachPath\Survey;
use Illuminate\Database\Eloquent\Model;
use App\Models\CoachPath\SurveyContentArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyContent extends Model
{
    use HasFactory;

    protected $table = 'surveyContent';
	public $primaryKey = 'surveyContentId';

    /**
     * Get all of the surveyContentData for the SurveyContent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyContentData()
    {
        return $this->hasMany(SurveyContentData::class, 'surveyContentId', 'surveyContentId');
    }

    /**
     * Get all of the surveyContentArea for the SurveyContent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyContentArea()
    {
        return $this->hasMany(SurveyContentArea::class, 'surveyContentAreaId', 'surveyContentAreaId');
    }

    /**
     * Get all of the survey for the SurveyContent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function survey()
    {
        return $this->hasMany(Survey::class, 'surveyId', 'surveyId');
    }
}
