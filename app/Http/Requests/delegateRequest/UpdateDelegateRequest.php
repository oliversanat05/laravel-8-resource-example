<?php

namespace App\Http\Requests\delegateRequest;

use App\Rules\CheckEmailExists;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDelegateRequest extends FormRequest
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
            'name' => ['required'],
            'preferedCommunication' => ['required'],
            'preferenceType' => ['required'],
            'frequency' => ['required'],
            'email' => ['required'],
            'dueDate' => ['required'],
            // 'dateRange' => ['required_if:anotherfield,value'],
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d'],
            'cell' => ['numeric', 'digits:10']

        ];
    }
}
