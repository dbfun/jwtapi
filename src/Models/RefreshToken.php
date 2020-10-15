<?php

namespace Dbfun\JwtApi\Models;

use DateTime;

class RefreshToken extends \Laravel\Passport\RefreshToken
{
    public function isExpired()
    {
        return $this->expires_at < new DateTime();
    }
}
