<?php

declare(strict_types=1);

use Avax\Container\Containers\Application;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

/** @var Application $application */
$application = app()->get(id: Application::class);
$application->run();
