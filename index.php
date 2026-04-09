<?php

declare(strict_types=1);

use Avax\Container\Http\HttpApplication;

require_once __DIR__ . '/vendor/autoload.php';

/** @var HttpApplication $app */
$app = require_once __DIR__ . '/bootstrap/bootstrap.php';

$app->run();
