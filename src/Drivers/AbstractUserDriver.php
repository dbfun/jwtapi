<?php

namespace Dbfun\JwtApi\Drivers;

use Exception;
use Illuminate\Foundation\Auth\User;

abstract class AbstractUserDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $credentials
     * @return User
     * @throws Exception
     */
    abstract public function auth(array $credentials): User;
}
