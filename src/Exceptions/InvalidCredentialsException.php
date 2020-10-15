<?php

namespace Dbfun\JwtApi\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message = "Invalid credentials";
}
