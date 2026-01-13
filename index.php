<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

/** @var \Avax\Container\Http\HttpApplication $app */
$app = require_once __DIR__.'/bootstrap/bootstrap.php';

$app->run();
