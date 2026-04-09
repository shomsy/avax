<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Container;

assertInstanceOf(
    Container::class,
    makeTestContainer(CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FPM)),
    'FPM async target should stay supported.'
);
assertInstanceOf(
    Container::class,
    makeTestContainer(CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_WORKER)),
    'Worker async target should stay supported.'
);
assertThrows(
    InvalidArgumentException::class,
    static function () : void {
        makeTestContainer(CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_COROUTINE));
    },
    'Coroutine async targets should fail with an explicit boundary error.'
);
assertThrows(
    InvalidArgumentException::class,
    static function () : void {
        makeTestContainer(CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FIBER));
    },
    'Fiber async targets should fail with an explicit boundary error.'
);

echo basename(__FILE__) . " ok\n";
