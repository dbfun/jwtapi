<?php

namespace Dbfun\JwtApi;

use Dbfun\JwtApi\Controllers\JwtApiController;
use Route;

class JwtApi
{
    /**
     * @param array $options
     */
    public static function routes(array $options = [])
    {
        $defaultOptions = [
            "prefix" => "/api/auth"
        ];

        $options = array_merge($defaultOptions, $options);

        Route::prefix($options["prefix"])->group(function () {
            Route::middleware(Middlewares\JwtApi::class)->post("getToken", [JwtApiController::class, "getToken"]);
            Route::post("refreshToken", [JwtApiController::class, "refreshToken"]);
            Route::get("getCertificate", [JwtApiController::class, "getCertificate"]);
            Route::post("checkToken", [JwtApiController::class, "checkToken"]);
        });
    }
}
