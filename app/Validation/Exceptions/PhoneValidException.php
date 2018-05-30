<?php

namespace App\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class PhoneValidException extends ValidationException
{
    public static $defaultTemplates = [
     self::MODE_DEFAULT => [
      self::STANDARD =>'Please check phone number(0xx-xxx-xxxx)or(0x-xxx-xxxx).',
     ],
    ];
}