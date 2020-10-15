<?php

namespace Dbfun\JwtApi\Exceptions;

use Exception;

class EmptyPasswordException extends Exception
{
    protected $message = "Password is empty";
}
