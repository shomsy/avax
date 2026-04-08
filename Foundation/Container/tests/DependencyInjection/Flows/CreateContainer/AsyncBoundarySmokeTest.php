<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;

assertInstanceOf(
    \Avax\Container\Container::class,
    makeTestContainer(CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FPM)),
    'FPM async target should stay supported.'
);
assertInstanceOf(
    \Avax\Container\Container::class,
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
