<?php

namespace App\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class PhoneValid extends AbstractRule
{
    protected $phone;

    public function validate($input)
    {
        if (!filter_var($input, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^\+?(972|0)(\-)?0?(([23489]{1}\d{7})|[5]{1}\d{8})$/")))) {
            return false;
        } else {
            return true;
        }

    }
}
