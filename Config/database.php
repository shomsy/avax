<?php

declare(strict_types=1);

return [
    'default' => env(key: 'DB_DEFAULT_CONNECTION', default: 'mysql'), // Default database connection name

    'connections' => [
        'mysql' => [
            'name'       => env(key: 'DB_NAME', default: 'cashback'),
            'username'   => env(key: 'DB_USER', default: 'root'),
            'password'   => env(key: 'DB_PASSWORD', default: 'root'),
            'connection' => 'mysql:host=' . env(key: 'DB_HOST', default: '127.0.0.1') .
                ';port=' . env(key: 'DB_PORT', default: '3306') .
                ';dbname=' . env(key: 'DB_NAME', default: 'cashback'),
            'options'    => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        'pgsql' => [
            'name'       => env(key: 'PG_DB_NAME', default: 'cashback'),
            'username'   => env(key: 'PG_DB_USER', default: 'postgres'),
            'password'   => env(key: 'PG_DB_PASSWORD', default: 'postgres'),
            'connection' => 'pgsql:host=' . env(key: 'PG_DB_HOST', default: '127.0.0.1') .
                ';port=' . env(key: 'PG_DB_PORT', default: '5432') .
                ';dbname=' . env(key: 'PG_DB_NAME', default: 'cashback'),
            'options'    => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        'sqlite' => [
            'connection' => 'sqlite:' . env(key: 'SQLITE_DB_PATH', default: 'database.sqlite'),
            'options'    => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        'sqlsrv' => [
            'name'       => env(key: 'MSSQL_DB_NAME', default: 'cashback'),
            'username'   => env(key: 'MSSQL_DB_USER', default: 'sa'),
            'password'   => env(key: 'MSSQL_DB_PASSWORD', default: 'password'),
            'connection' => 'sqlsrv:Server=' . env(key: 'MSSQL_DB_HOST', default: 'localhost') .
                ',' . env(key: 'MSSQL_DB_PORT', default: '1433') .
                ';Database=' . env(key: 'MSSQL_DB_NAME', default: 'cashback'),
            'options'    => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],
    ],
];
