<?php

namespace Dbfun\JwtApi\Passport;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
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

    public function __toString()
    {
        return (string)$this->convertToJWT($this->privateKey);
    }

    public function addClaim(string $claim, $value): self
    {
        $this->claims[$claim] = $value;
        return $this;
    }

    /**
     * Generate a JWT from the access token
     *
     * @param CryptKey $privateKey
     *
     * @return Token
     * @see \League\OAuth2\Server\Entities\Traits\AccessTokenTrait::convertToJWT
     */
    private function convertToJWT(CryptKey $privateKey)
    {
        $builder = (new Builder())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(\time())
            ->canOnlyBeUsedAfter(\time())
            ->expiresAt($this->getExpiryDateTime()->getTimestamp())
            ->relatedTo((string)$this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        foreach ($this->claims as $claim => $value) {
            $builder->withClaim($claim, $value);
        }

        return $builder->getToken(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()));
    }

}
