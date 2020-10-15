# Introduction

Obtaining Jwt tokens from Laravel Passport via API by login and password. No `client_id` and `client_secret` required.

Token claims customization. No Guzzle dependency.

## Installation

```bash
composer require dbfun/jwtapi
# Choose "yes":
php artisan passport:install --uuids
```

Save Client ID for future reference.

```
Personal access client created successfully.
Client ID: ..............
Client secret: ..............
```

`config/auth.php`:

```php
'api' => [
    'driver' => 'passport',
    'provider' => 'users',
    'hash' => false,
],
```

Add to `config/app.php`:

```php
/*
 * Package Service Providers...
 */

Dbfun\JwtApi\JwtApiServiceProvider::class,
```

Add to `app/Providers/AuthServiceProvider.php`:

```php
\Dbfun\JwtApi\JwtApi::routes();
// \Laravel\Passport\Passport::tokensCan(/* List of scopes */);
```

Add to model file `User.php`:

```php
use Dbfun\JwtApi\Traits\JwtApiTokenTrait;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use JwtApiTokenTrait;
    use HasApiTokens;
    
    public function scopes(): array
    {
        // put here the logic
        return [];
    }
}
```

### Keys

You can generate private and public keys via this snippet:

```bash
mkdir /tmp/keys
ssh-keygen -t rsa -f /tmp/keys/key
openssl rsa -in /tmp/keys/key -pubout > /tmp/keys/key.pub
cat /tmp/keys/*
```

Add to `.env`:

```
PASSPORT_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----\n(private key)\n-----END RSA PRIVATE KEY-----"
PASSPORT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n(public key)\n-----END PUBLIC KEY-----"
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=
```

Use personal Client ID from above. Or look in the `oauth_clients` table. Or generate new: 

```bash
php artisan passport:client --personal
```

## Auth providers

The auth provider is configured through configuration `auth.guards.api.provider`:

```php
return [
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',                // <-- provider name
            'hash' => false,
        ],
    ],
    'providers' => [
        'users' => [                              // <-- "users" provider config
            'driver' => 'eloquent',               // <-- driver
            'model' => \App\Models\User::class,   // <-- driver option
        ],
    ],
];
```
