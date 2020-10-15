<?php

namespace Dbfun\JwtApi\Middlewares;

use Closure;
use Dbfun\JwtApi\Events\AuthFail;
use Dbfun\JwtApi\Events\AuthOk;
use Dbfun\JwtApi\Exceptions\EmptyLoginException;
use Dbfun\JwtApi\Exceptions\EmptyPasswordException;
use Dbfun\JwtApi\Exceptions\InvalidCredentialsException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class JwtApi extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $user = $this->authUser($request->json("login"), $request->json("password"));
            event(new AuthOk($user, $request->toArray()));
        } catch (EmptyLoginException $e) {
            return response()->json([
                "error" => $e->getMessage()
            ])->setStatusCode(401);
        } catch (EmptyPasswordException $e) {
            return response()->json([
                "error" => $e->getMessage()
            ])->setStatusCode(401);
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                "error" => $e->getMessage()
            ])->setStatusCode(401);
        } catch (Exception $e) {
            Log::channel("stderr")->error("User auth fail", [
                "message" => $e->getMessage(),
                "error" => (string)$e,
            ]);

            return response()->json([
                "error" => $e->getMessage()
            ])->setStatusCode(500);
        } finally {
            if (isset($e)) {
                event(new AuthFail($e->getMessage(), $request->toArray()));
            }
        }

        return $next($request);
    }

    /**
     * @param string $login
     * @param string $password
     * @param string $provider
     * @return User
     * @throws \Exception
     *
     * Passport auth example: @see \Laravel\Passport\Bridge\UserRepository
     */
    public function authUser(?string $login, ?string $password, ?string $provider = null): User
    {
        $provider = $provider ?? config("auth.guards.api.provider");
        $driver = config(sprintf("auth.providers.%s.driver", $provider));
        $config = config(sprintf("auth.providers.%s", $provider));

        $driverClassName = sprintf("\\Dbfun\\JwtApi\\Drivers\\%s", ucfirst($driver));
        if (!class_exists($driverClassName)) {
            throw new \Exception(sprintf("Wrong driver type: %s", $driver));
        }

        /** @var \Dbfun\JwtApi\Drivers\AbstractUserDriver $driverClassName */
        $auth = new $driverClassName($config);
        $user = $auth->auth(["login" => $login, "password" => $password]);
        Auth::login($user);
        return $user;
    }
}
