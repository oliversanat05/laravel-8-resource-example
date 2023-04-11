<?php

namespace App\Models\CoachPath;

use App\Models\CoachPath\SurveyContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyContentData extends Model
{
    use HasFactory;

    protected $table = 'surveyContentData';
    public $primaryKey = 'surveyContentDataId';

    protected $guarded = [];

    /**
     * Get the surveyContent associated with the SurveyContentData
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function surveyContent()
    {
        return $this->hasMany(SurveyContent::class, 'surveyContentId', 'surveyContentId');
    }
}
