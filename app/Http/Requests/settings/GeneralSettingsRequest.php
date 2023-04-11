<?php

namespace App\Http\Requests\settings;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneralSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'maxPerformAv' => ['numeric', 'gt:minPerformAv'],
            'minPerformAv' => ['numeric', 'lt:maxPerformAv'],
            'defaultPage' => ['required', 'exists:pages,route'],
            'vmapVisionMission' => ['required'],
            'completeIteminVmap' => ['required'],
            'completeIteminTracking' => ['required'],
            'timePeriod' => ['required'],
        ];
    }

    /**
     * update the general setting data of the logged in user
     *
     * @return array
     */
    public function generalSettings()
    {
        return [
            'rangeStartDate' => isset($this->timePeriod[0]) ? Carbon::parse($this->timePeriod[0])->format('Y-m-d') : '',
            'rangeEndDate' => isset($this->timePeriod[1]) ? Carbon::parse($this->timePeriod[1])->format('Y-m-d') : '',
            'workingDaysWeekly' => !is_null($this->workingDaysWeekly) ? implode(',',
                $this->workingDaysWeekly) : '',
            'vmapVisionMission' => $this->vMapVisionMission,
            'defaultPage' => $this->defaultPage,
            'defaultVmap' => $this->defaultVmap,
            'completeIteminVmap' => $this->completeIteminVmap,
            'completeIteminTracking' => $this->completeIteminTracking,
            'maxPerformAv' => $this->maxPerformAv,
            'minPerformAv' => $this->minPerformAv,
        ];
    }

    /**
     * update the default profile of the user
     *
     * @return void
     */
    public function updateDefaultProfile()
    {
        return [
            'default_profile' => $this->defaultProfile,
        ];
    }

    public function messages()
    {
        return [
            'maxPerformAv.gt' => 'The maximum delegate performance should be greater the minimum delegate performance',
            'minPerformAv.lt' => 'The minimum delegate performance should be less the maximum delegate performance',
            'vmapVisionMission' => 'The activity Avatar is required',
            'defaultPage' => 'The :attribute is required',
        ];
    }

    public function failedValidation(Validator $validation)
    {
        $response = new JsonResponse(
          [
            "status" => false,
            "message" => $validation->errors(),
          ],
          JsonResponse::HTTP_UNPROCESSABLE_ENTITY
        );

        throw new HttpResponseException( $response );
    }
}
