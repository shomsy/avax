<?php

declare(strict_types=1);

// Load composer autoloader FIRST
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/Foundation/Helpers/helpers.php';

use Avax\Container\Features\Operate\Boot\Application;
use Avax\HTTP\Context\PhpGlobalsProvider;
use Avax\HTTP\Session\Shared\Security\NativeSessionIdProvider;

$app = Application::start(root: __DIR__)
    ->exposeWeb(path: __DIR__ . '/Presentation/HTTP/routes/web.routes.php')
    ->build();

// Register available Foundation providers from Config/services.php
$services = require __DIR__ . '/Config/services.php';
foreach ($services as $providerClass) {
    if (class_exists($providerClass) && is_subclass_of($providerClass, \Avax\Container\Features\Operate\Boot\ServiceProvider::class)) {
        $app->register($providerClass);
    }
}

return $app;
