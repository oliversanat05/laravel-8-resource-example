<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IsGridTitleUniqueForUser implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return (DB::table('relational_grids')
        ->where(function ($query) {
            $query->where('user_id','=',Auth::user()->user_id);
        })
        ->where('idea_title','=',$value)   
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
