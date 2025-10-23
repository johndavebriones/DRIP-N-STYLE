<?php
return [
    // SESSION KEYS
    'session_keys' => [
        'id'   => 'user_id',
        'name' => 'user_name',
        'role' => 'user_role',
    ],

    // DEFAULT ROLE
    'default_role' => 'customer',

    // PATH REDIRECT
    'redirects' => [
        'after_login_admin'    => '../../admin/dashboard.php',
        'after_login_customer' => '../../public/shop.php',
        'after_register'       => '../../public/auth.php',
        'after_logout'         => '../../public/index.php',
        'on_error'             => '../../public/auth.php',
    ],

    // PASSWORD HASHING
    'password_algo' => PASSWORD_DEFAULT,
];
