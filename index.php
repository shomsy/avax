<?php

declare(strict_types=1);

use Avax\Container\Features\Operate\Boot\Application;

require_once __DIR__ . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/bootstrap.php';

$app->run();
