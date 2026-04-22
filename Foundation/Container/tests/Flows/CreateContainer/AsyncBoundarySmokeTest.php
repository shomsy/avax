<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Container;

assertInstanceOf(
    expectedClass: Container::class,
    value        : makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FPM)),
    message      : 'FPM async target should stay supported.'
);
assertInstanceOf(
    expectedClass: Container::class,
    value        : makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_WORKER)),
    message      : 'Worker async target should stay supported.'
);
assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function () : void {
        makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_COROUTINE));
    },
    message      : 'Coroutine async targets should fail with an explicit boundary error.'
);
assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function () : void {
        makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FIBER));
    },
    message      : 'Fiber async targets should fail with an explicit boundary error.'
);

echo basename(__FILE__) . " ok\n";
