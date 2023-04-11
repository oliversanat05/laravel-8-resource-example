<?php

namespace App\Services\CoachPath;

use App\Models\CoachPath\Survey;
use App\Models\CoachPath\SurveyContent;
use App\Models\CoachPath\SurveyContentArea;
use App\Models\CoachPath\SurveyContentData;
use Auth;
use Carbon\Carbon;
use Config;

class CoachPathService
{

    /**
     * for storing coaching readiness date
     *
     * @param [type] $data
     * @return void
     */
    public function storeCoachingReadinessDate($data, $type)
    {

        $questionArray = [];
        switch ($type) {
            case 'coach':
                $questionArray = [
                    'surveyContentData' => 0,
                    'surveyContentId' => 1,
                    'surveyDate' => $data,
                    'userId' => Auth::user()->user_id,
                ];
                break;

            case 'sweet-spot-analysis':
                $questionArray = [
                    'surveyContentData' => 0,
                    'surveyContentId' => 12,
                    'surveyDate' => $data,
                    'userId' => Auth::user()->user_id,
                ];
                break;

            case 'core-discipline':
                $questionArray = [
                    'surveyContentData' => 0,
                    'surveyContentId' => 19,
                    'surveyDate' => $data,
                    'userId' => Auth::user()->user_id,
                ];
                break;

            default:
                return "ldfjg";
        }

        $coachingReadiness = SurveyContentData::create($questionArray);

        if ($coachingReadiness) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * for deleting the coahching readiness date
     *
     * @param Date $date
     * @return boolean
     */
    public function deleteCoachingReadiness($date, $type)
    {

        $getSurveyContent = [];

        switch ($type) {
            case 'coach':
                $getSurveyContent = SurveyContent::where('surveyId', Config::get('statistics.startHere.coachingReadiness'))->get(['surveyContentId'])->toArray();
                break;

            case 'sweet-spot-analysis':
                $getSurveyContent = SurveyContent::where('surveyId', Config::get('statistics.startHere.sweetSpotAnalysis'))->get(['surveyContentId'])->toArray();
                break;
            case 'core-discipline':
                $getSurveyContent = SurveyContent::where('surveyId', Config::get('statistics.startHere.coreDisciplines'))->get(['surveyContentId'])->toArray();
                break;

            default:
                return "lskfg";
        }

        $deleteCoachingReadiness = SurveyContentData::where('userId', Auth::user()->user_id)
            ->where('surveyDate', $date)
            ->whereIn('surveyContentId', $getSurveyContent)
            ->delete();
        return $deleteCoachingReadiness;
    }

    /**
     * for editing the coaching readiness date
     *
     * @param [type] $date
     * @return void
     */
    public function editCoachingReadinessDate($date, $eDate, $type)
    {
        $formatDate = Carbon::parse($date)->format('Y-m-d');

        switch ($type) {
            case 'coach':
                $checkFormDateExists = $this->checkFormDateExists($date, Config::get('statistics.startHere.coachingReadiness'));

                if ($checkFormDateExists) {
                    $editResponse = SurveyContentData::where('userId', Auth::user()->user_id)
                        ->with(['surveyContent' => function ($query) {
                            $query->where('surveyId', Config::get('statistics.startHere.coachingReadiness'));
                        }])->where('surveyDate', $eDate)->update(['surveyDate' => $date]);

                    if ($editResponse) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                break;

            case 'sweet-spot-analysis':
                $checkFormDateExists = $this->checkFormDateExists($date, Config::get('statistics.startHere.sweetSpotAnalysis'));

                if ($checkFormDateExists) {
                    $editResponse = SurveyContentData::where('userId', Auth::user()->user_id)
                        ->with(['surveyContent' => function ($query) {
                            $query->where('surveyId', Config::get('statistics.startHere.sweetSpotAnalysis'));
                        }])->where('surveyDate', $eDate)->update(['surveyDate' => $date]);

                    if ($editResponse) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

                break;
            case 'core-discipline':
                $checkFormDateExists = $this->checkFormDateExists($date, Config::get('statistics.startHere.coreDisciplines'));

                if ($checkFormDateExists) {
                    $editResponse = SurveyContentData::where('userId', Auth::user()->user_id)
                        ->with(['surveyContent' => function ($query) {
                            $query->where('surveyId', Config::get('statistics.startHere.coreDisciplines'));
                        }])->where('surveyDate', $eDate)->update(['surveyDate' => $date]);

                    if ($editResponse) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

                break;

            default:
                return "lsjh";
        }

    }

    /**
     * check if the form date already Exists
     *
     * @param Date $date
     * @param integer $surveyId
     * @return void
     */
    public function checkFormDateExists($date, $surveyId)
    {
        $checkDateExists = SurveyContentData::where('userId', Auth::user()->user_id)
            ->where('surveyDate', $date)->whereHas('surveyContent')
            ->whereHas('surveyContent', function ($query) use ($surveyId) {
                $query->whereIn('surveyContentId', [$surveyId]);
            })->exists();

        if (!$checkDateExists) {
            return true;
        }
        return false;
    }

    public function getInitialFormData($contentId)
    {
        $dataArray['surveyContentQuestions'] = self::getSurveyContent($contentId);
        $dataArray['surveyContentArea'] = self::getSurveyContentArea($dataArray['surveyContentQuestions']);
        $dataArray['surveyContentData'] = self::getSurveyContentData($dataArray['surveyContentQuestions']);

        return $dataArray;
    }

    /**
     * to get the survey id of the form i.e:, coaching readiness, sweet spot ananlysis, core disciplines
     *
     * @param integer $contentId
     * @return collection
     */
    public static function getSurveyContent($contentId)
    {
        $surveyContent = SurveyContent::where('surveyId', $contentId)->get();
        return $surveyContent;
    }

    /**
     * to get the survey content area of the form i.e:, the questions displayed on the date
     *
     * @param array $data
     * @return array
     */
    public static function getSurveyContentArea($data)
    {
        $surveyContentArea = $topics = [];

        foreach ($data as $key => $value) {
            if (isset($value['surveyContentAreaId'])) {
                $surveyContentArea[$value['surveyContentAreaId']] = $value['surveyContentAreaId'];
            }
        }

        $descriptions = SurveyContentArea::whereIn('surveyContentAreaId', $surveyContentArea)->get(['description', 'surveyContentAreaId']);

        if (!empty($descriptions->toArray())) {
            foreach ($descriptions->toArray() as $key => $topic) {

                $topics[$topic['surveyContentAreaId']] = $topic['description'];
            }
        }

        return $topics;
    }

    /**
     * to get the survey content data i.e:, form data stored
     *
     * @param array $data
     * @return collection
     */
    public function getSurveyContentData($data)
    {
        $surveyContentData = SurveyContentData::whereIn('surveyContentId', $data->pluck('surveyContentId')->toArray())->where('userId', Auth::user()->user_id)->orderBy('surveyDate', 'DESC')->get()->toArray();

        $surveyData = [];
        foreach ($surveyContentData as $key => $survey) {
            $surveyData[$key][$survey['surveyDate']] = $survey;
        }

        $surveyArray = [];
        foreach ($surveyData as $skey => $s) {
            foreach ($s as $vkey => $value) {
                if (isset($surveyArray[$vkey])) {
                    array_push($surveyArray[$vkey], $value);
                } else {
                    $surveyArray[$vkey] = [$value];
                }
            }

        }

        return $surveyArray;
    }

    /**
     * save the coaching readiness questions data
     *
     * @param [type] $data
     * @return void
     */
    public function saveCoachingReadinessQuestionsData($data, $surveyDate)
    {
        $dataArray = [];

        $survey = '';

        foreach ($data as $key => $value) {
            $question = explode('-', $key);
            $checkSurveyExists = SurveyContentData::whereIn('surveyContentId', [$question[1]])
                ->where('userId', Auth::user()->user_id)->where('surveyDate', $surveyDate)->exists();

            if ($checkSurveyExists) {

                $survey = SurveyContentData::where('surveyContentId', $question[1])
                    ->where('userId', Auth::user()->user_id)
                    ->where('surveyDate', $surveyDate)->update(['surveyContentData' => $value]);

            } else {
                $survey = SurveyContentData::create([
                    'surveyDate' => $surveyDate,
                    'surveyContentData' => $value,
                    'surveyContentId' => $question[1],
                    'userId' => Auth::user()->user_id,
                ]);
            }

        }
        return $survey;
    }
}
