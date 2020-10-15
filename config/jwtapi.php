<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Access and refresh tokens TTL
    |--------------------------------------------------------------------------
    */
    'accessTokenTtl' => env('PASSPORT_ACCESS_TOKEN_TTL', 1800),
    'refreshTokenTtl' => env('PASSPORT_REFRESH_TOKEN_TTL', 864000),

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application.
    |
    */
    'private_key' => str_replace('\n', "\n", env('PASSPORT_PRIVATE_KEY')),
    'public_key' => str_replace('\n', "\n", env('PASSPORT_PUBLIC_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Personal Access Client
    |--------------------------------------------------------------------------
    |
    | If you enable client hashing, you should set the personal access client
    | ID and unhashed secret within your environment file. The values will
    | get used while issuing fresh personal access tokens to your users.
    |
    */

    'personal_access_client' => [
        'id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
        // 'secret' - not needed
    ],
];
