<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/DRIP-N-STYLE/');
}

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
        'after_login_customer' => '../../Public/shop.php',
        'after_register'       => '../../Public/auth.php',
        'after_logout'         => '../../Public/index.php',
        'on_error'             => '../../Public/auth.php',
    ],

    // PASSWORD HASHING
    'password_algo' => PASSWORD_DEFAULT,
];
