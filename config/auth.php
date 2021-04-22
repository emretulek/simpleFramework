<?php
return [
    'table' => 'users',
    'token_name' => 'user_token',
    'user_where' => [
        'status' => 1,
        'deleted_at' => null
    ],
    'session_not_stored' => [
        'password'
    ]
];
