<?php

namespace App\Exceptions;

use Throwable;

class ApiExpiredException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Api Key Has Expired", 1000, null);
    }
}
