<?php

namespace App\Models\CoachPath;

use App\Models\CoachPath\SurveyContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Survey extends Model
{
    use HasFactory;

    protected $table = 'survey';
    protected $primaryKey = 'surveyId';

    /**
     * Get all of the surveyContent for the Survey
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyContent()
    {
        return $this->hasMany(SurveyContent::class, 'surveyId', 'surveyId');
    }


}
