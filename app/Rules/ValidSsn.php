<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Controllers\CryptoController;
use App\Client;

class ValidSsn implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $id;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $ssn = (new CryptoController)->my_simple_crypt($this->removeSpecial($value), 'e');

        if ($this->id != null) {
            $unique_ssn = Client::where(['ssn' => $ssn, 'deleted' => '0'])->where('id', '!=', $this->id)->first();
        } else {
            $unique_ssn = Client::where(['ssn' => $ssn, 'deleted' => '0'])->first();
        }

        if (empty($unique_ssn)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The SSN has already been taken.';
    }

    public function removeSpecial($value) {
        if (!empty($value)) {
            $non_digits = [' ', '-', '.', '_'];
            $nums = str_replace($non_digits, '', $value);
            return $nums;
        }
    }

    public function __construct($id){
        return $this->id = $id;
    }

}
