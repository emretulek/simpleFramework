<?php
return [
    'authorization_type'    => 'Auth', // [Auth, AuthJWT]
    'table'                 => 'users',
    'token_name'            => 'user_token',
    'multi_device'          => true,
    'user_where'            => [
        'status'     => 1,
        'deleted_at' => null
    ],
    'session_not_stored'    => [
        'password'
    ],
    'login_attempt'         => true,
    'login_attempt_max'     => 3,
    'login_attempt_timeout' => 60,
    'jwt'                   => [
        'header'  => 'Auth-JWT',
        'secret'  => 'eThWmZq4t6w9z$C&F)J@NcRfUjXn2r5u',
        'algo'    => 'HS256',
        'payload' => [
            'iat' => time(),
            'exp' => time() + (60 * 15),
            'iss' => 'https://domain.com/'
        ]
    ]
];
