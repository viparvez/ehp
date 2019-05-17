<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateDOB implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $date;

    public function __construct()
    {
        
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
        if ($value != null) {
            $value = (new \App\Custom\Custom)->convertDate($value, "Y-m-d");
            if ($value >= date('Y-m-d')) {
                return false;
            }

            return true;
        } 

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The date of birth must be equal to or older than ".date('m-d-Y');
    }
}
