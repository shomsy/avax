<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Container;

assertInstanceOf(
    expectedClass: Container::class,
    value        : makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FPM)),
    message      : 'FPM async target should stay supported.',
);
assertInstanceOf(
    expectedClass: Container::class,
    value        : makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_WORKER)),
    message      : 'Worker async target should stay supported.',
);
assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function (): void {
        makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_COROUTINE));
    },
    message      : 'Coroutine async targets should fail with an explicit boundary error.',
);
assertThrows(
    expectedClass: InvalidArgumentException::class,
    callback     : static function (): void {
        makeTestContainer(config: CreateContainerConfig::create(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FIBER));
    },
    message      : 'Fiber async targets should fail with an explicit boundary error.',
);

echo basename(path: __FILE__) . " ok\n";
