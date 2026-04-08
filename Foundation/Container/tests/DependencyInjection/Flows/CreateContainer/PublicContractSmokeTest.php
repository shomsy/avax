<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Container;
use Avax\Container\ContainerInterface;

$expectedMethods = [
    'alias',
    'bind',
    'bootProviders',
    'call',
    'canInject',
    'closeScope',
    'compileContainer',
    'compileReport',
    'decorate',
    'debugAliases',
    'debugGraph',
    'debugPlan',
    'debugScope',
    'debugService',
    'debugTags',
    'defer',
    'describeService',
    'env',
    'exportMetrics',
    'extend',
    'flush',
    'flushCompiled',
    'forContext',
    'get',
    'grouped',
    'has',
    'hasAlias',
    'injectInto',
    'inspectInjection',
    'instance',
    'isCompiled',
    'isDeferred',
    'isLazy',
    'isWarmedUp',
    'lazy',
    'make',
    'openScope',
    'rebuildCompiled',
    'reset',
    'runtimeReport',
    'scoped',
    'scopes',
    'singleton',
    'tag',
    'tagged',
    'validate',
    'warmCompiled',
    'when',
];

foreach ($expectedMethods as $method) {
    assertTrue(method_exists(ContainerInterface::class, $method), "ContainerInterface should expose [{$method}].");
    assertTrue(method_exists(Container::class, $method), "Container should implement [{$method}].");
}

$forbiddenLegacyMethods = [
    'beginScope',
    'endScope',
];

foreach ($forbiddenLegacyMethods as $method) {
    assertTrue(! method_exists(ContainerInterface::class, $method), "Legacy public method [{$method}] must stay absent from ContainerInterface.");
    assertTrue(! method_exists(Container::class, $method), "Legacy public method [{$method}] must stay absent from Container.");
}

echo basename(__FILE__) . " ok\n";
