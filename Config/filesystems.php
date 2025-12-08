<?php

declare(strict_types=1);

use Avax\Config\Architecture\DDD\AppPath;

return [
    'default' => env(key: 'FILESYSTEM_DISK', default: 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => AppPath::getRoot(),
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env(key: 'AWS_ACCESS_KEY_ID'),
            'secret'   => env(key: 'AWS_SECRET_ACCESS_KEY'),
            'region'   => env(key: 'AWS_DEFAULT_REGION'),
            'bucket'   => env(key: 'AWS_BUCKET'),
            'url'      => env(key: 'AWS_URL'),
            'endpoint' => env(key: 'AWS_ENDPOINT'),
        ],
    ],
];
