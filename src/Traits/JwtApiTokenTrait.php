<?php

namespace Dbfun\JwtApi\Traits;

use DateTime;
use DateTimeImmutable;
use Dbfun\JwtApi\Passport\AccessToken;
use Illuminate\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\RefreshToken;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

trait JwtApiTokenTrait
{
    /**
     * @param CryptKey $privateKey
     * @param Client $client
     * @return array
     * @throws OAuthServerException
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function createTokens(CryptKey $privateKey, Client $client): array
    {
        $token = new AccessToken($this->getKey(), $this->scopes(), $client);
        $token->setIdentifier($this->generateUniqueIdentifier());
        $token->setPrivateKey($privateKey);

        $date = DateTimeImmutable::createFromMutable((new DateTime())->add(Passport::tokensExpireIn()));
        $token->setExpiryDateTime($date);

        $token->setUserIdentifier($this->getKey());
        $token->addClaim("email", $this->email);

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository, new Dispatcher);
        $accessTokenRepository->persistNewAccessToken($token);

        $refreshToken = $this->issueRefreshToken($token);

        return [
            "accessToken" => $token,
            "refreshToken" => $refreshToken
        ];
    }

    /**
     * @param int $length
     * @return string
     * @throws OAuthServerException
     */
    private function generateUniqueIdentifier($length = 40): string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (\TypeError $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Error $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            throw OAuthServerException::serverError('Could not generate a random string');
        }
    }

    /**
     * @param AccessTokenEntityInterface $accessToken
     * @return RefreshToken
     * @throws OAuthServerException
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    private function issueRefreshToken(AccessTokenEntityInterface $accessToken): RefreshToken
    {
        $maxGenerationAttempts = 10;
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $refreshToken = $refreshTokenRepository->getNewRefreshToken();

        $date = DateTimeImmutable::createFromMutable((new DateTime())->add(Passport::refreshTokensExpireIn()));
        $refreshToken->setExpiryDateTime($date);
        $refreshToken->setAccessToken($accessToken);

        while ($maxGenerationAttempts-- > 0) {
            $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $refreshTokenRepository->persistNewRefreshToken($refreshToken);

                return $refreshToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }
    }


}
