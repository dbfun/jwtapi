<?php

namespace Dbfun\JwtApi\Drivers;

use Illuminate\Foundation\Auth\User;

class Ldap extends AbstractUserDriver
{
    /**
     * {@inheritdoc}
     */
    public function auth(array $credentials): User
    {
        // not implemented
    }
}
