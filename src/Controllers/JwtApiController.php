<?php

namespace Dbfun\JwtApi\Controllers;

use App\Http\Controllers\Controller;
use Dbfun\JwtApi\Models\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;

class JwtApiController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     *
     * @see \App\Providers\AuthServiceProvider::boot()
     */
    public function getToken(Request $request)
    {
        $user = Auth::user();

        $privateKey = new CryptKey(config("jwtapi.private_key"));
        $clientId = config("jwtapi.personal_access_client.id");

        $clientRepository = app()->make(ClientRepository::class);
        if (!$clientRepository->findActive($clientId)) {
            abort(500, "Personal access client not exists");
        }
        $client = new Client($clientId, sprintf("%s-%s", $user->email, now()), null);

        $tokens = $user->createTokens($privateKey, $client);

        return response()->json([
            "access_token" => (string)$tokens["accessToken"],
            "refresh_token" => $tokens["refreshToken"]->getIdentifier(),
            "expires_at" => Carbon::parse(
                $tokens["accessToken"]->getExpiryDateTime()
            )->toDateTimeString()
        ])->setStatusCode(201);
    }


    public function refreshToken(Request $request)
    {
        $refreshToken = RefreshToken::find($request->refresh_token);

        if (!$refreshToken || $refreshToken->revoked || $refreshToken->isExpired()) {
            return response()->json([
                "error" => "refresh_token not found",
            ])->setStatusCode(404);
        }

        $accessToken = $refreshToken->accessToken()->first();

        if (!$accessToken) {
            return response()->json([
                "error" => "access_token not found",
            ])->setStatusCode(404);
        }

        Auth::login($accessToken->user);

        $accessToken->revoke();
        $refreshToken->revoke();

        return $this->getToken($request);
    }

    public function getCertificate(Request $request)
    {
        if ($request->query("format") === "raw") {
            return response()->make(config("jwtapi.public_key"));
        }
        return response()->json([
            "certificate" => config("jwtapi.public_key"),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @see \Lcobucci\JWT\Claim\Basic::class
     * @see \Lcobucci\JWT\Token::verify()
     */
    public function checkToken(Request $request)
    {
        $pubKey = config("jwtapi.public_key");

        $jwt = new Parser();
        $token = $jwt->parse($request->json("access_token"));

        $payload = [];
        foreach ($token->getClaims() as $claimName => $claim) {
            $payload[$claimName] = $claim->getValue();
        }

        $accessToken = Token::find($payload["jti"]);

        return response()->json([
            "validation_status" => $token->verify(new Sha256(), $pubKey) ? "valid" : "invalid",
            "header" => $token->getHeaders(),
            "payload" => $payload,
            "isExpired" => $token->isExpired(),
            "isRevoked" => $accessToken ? $accessToken->revoked : null
        ]);
    }
}
