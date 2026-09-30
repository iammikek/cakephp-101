<?php
declare(strict_types=1);

use function Cake\Core\env;

return [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'Security' => [
        'salt' => env('SECURITY_SALT', 'dev-only-cakephp-101-security-salt-value'),
    ],
    'Datasources' => [
        'default' => [
            'driver' => \Cake\Database\Driver\Sqlite::class,
            'database' => ROOT . DS . 'database' . DS . 'database.sqlite',
        ],
        'test' => [
            'driver' => \Cake\Database\Driver\Sqlite::class,
            'database' => ROOT . DS . 'database' . DS . 'test.sqlite',
        ],
    ],
    'Jwt' => [
        'secret' => env('JWT_SECRET', 'dev-only-cakephp-101-jwt-secret-key'),
    ],
];
