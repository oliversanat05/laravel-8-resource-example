<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IsIdeaUnique implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $idea_type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($idea_type)
    {
        $this->idea_type = $idea_type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (DB::table('ideas')
        ->where(function ($query) {
            $query->where('user_id','=',Auth::user()->user_id)
                  ->orWhere('is_default','=',config('constants.isIdea.global'));
        })
        ->where('idea_title','=',$value)  
        ->where('idea_type','=',$this->idea_type)   
        ->where('deleted_at','=',NULL)
        ->doesntExist());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Metric with the same data, already exists.';
    }
}
