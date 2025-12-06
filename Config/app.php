<?php

declare(strict_types=1);

return [
    'namespaces' => [
        'DTO'          => 'Domain\DTO',
        'Entity'       => 'Domain\Entities',
        'Migrations'   => 'Infrastructure\Migrations',
        'Repositories' => 'Infrastructure\Repositories',
        'Services'     => 'Infrastructure\Services',
        'Controllers'  => 'Presentation\HTTP\Controllers',
    ],
    'paths'      => [
        'DTO'          => 'Domain/DTO',
        'Entity'       => 'Domain/Entities',
        'Migrations'   => 'Infrastructure/Migrations',
        'Repositories' => 'Infrastructure/Repositories',
        'Services'     => 'Infrastructure/Services',
        'Controllers'  => 'Presentation/HTTP/Controllers',
        'Stubs'        => 'Infrastructure/Foundation/Database/Migration/Stubs',
    ],

    'filePermissions' => 0666,
];
