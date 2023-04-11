<?php

namespace App\Rules;

use App\Models\wowModels\ResponsiblePerson;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\wowModels\StatementListing;

class IsResponsiblePersonUnique implements Rule
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
        return(ResponsiblePerson::where([['user_id','=',Auth::user()->user_id],['name','=',$value]])
        ->doesntExist());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Responsible Person already exists in database';
    }
}
