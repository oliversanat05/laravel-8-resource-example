<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsNotPastDate implements Rule
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
        
        $current=date("Y-m-d");
        return (strtotime($value)>=strtotime($current)) ;
         
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'date should be genuine,only  apply for future !';
    }
}
