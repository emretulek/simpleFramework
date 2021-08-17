<?php
return [
    'table' => 'users',
    'token_name' => 'user_token',
    'multi_device' => true,
    'user_where' => [
        'status' => 1,
        'deleted_at' => null
    ],
    'session_not_stored' => [
        'password'
    ],
    'login_attempt' => true,
    'login_attempt_max' => 10,
    'login_attempt_timeout' => 60
];
