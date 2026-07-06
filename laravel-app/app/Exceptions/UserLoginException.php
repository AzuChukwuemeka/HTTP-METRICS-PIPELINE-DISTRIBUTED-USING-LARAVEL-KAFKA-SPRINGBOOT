<?php

namespace App\Exceptions;

use Exception;

class UserLoginException extends Exception
{

    public function __construct()
    {
        parent::__construct('Invalid username or password');
    }
}
