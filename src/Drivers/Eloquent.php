<?php

namespace Dbfun\JwtApi\Drivers;

use Dbfun\JwtApi\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User;

class Eloquent extends AbstractUserDriver
{
    /**
     * {@inheritdoc}
     *
     * For more complex implementation @see \Laravel\Passport\Bridge\UserRepository::getUserEntityByUserCredentials
     */
    public function auth(array $credentials): User
    {
        $model = $this->config["model"];

        $user = $model::where("email", $credentials["login"])->first();

        if (!$user) {
            throw new InvalidCredentialsException;
        }

        if (!Hash::check($credentials["password"], $user->password)) {
            throw new InvalidCredentialsException;
        }

        return $user;
    }
}
