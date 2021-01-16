<?php

namespace Dbfun\JwtApi\Passport;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * Extends Passport Class Паспорта @see \Laravel\Passport\Bridge\AccessToken
 * Here you can add additional attributes to the JWT token
 */
class AccessToken extends \Laravel\Passport\Bridge\AccessToken
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    protected $claims = [];

    public function __toString(): string
    {
        return $this->convertToJWT()->toString();
    }

    public function addClaim(string $claim, $value): self
    {
        $this->claims[$claim] = $value;
        return $this;
    }

    /**
     * Generate a JWT from the access token
     *
     * @return Token
     * @see \League\OAuth2\Server\Entities\Traits\AccessTokenTrait::convertToJWT
     */
    private function convertToJWT()
    {
        $this->initJwtConfiguration();

        $builder = $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new \DateTimeImmutable())
            ->canOnlyBeUsedAfter(new \DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string)$this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        foreach ($this->claims as $claim => $value) {
            $builder->withClaim($claim, $value);
        }

        return $builder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

}
