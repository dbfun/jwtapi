<?php

namespace Dbfun\JwtApi\Exceptions;

use Exception;

class EmptyLoginException extends Exception
{
    protected $message = "Login is empty";
}
