<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SpecialChar implements Rule
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
        $re = '/[!"#$%&\'()*+,-.:;<\/=>?@€[\\\\\]^_`{|}~]/m';

        return (bool) preg_match($re, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Password should contain at least one of the special chars.';
    }
}
