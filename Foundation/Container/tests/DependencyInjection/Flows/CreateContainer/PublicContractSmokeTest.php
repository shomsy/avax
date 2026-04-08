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
    'debugExports',
    'debugGraph',
    'debugImports',
    'debugPlan',
    'debugScope',
    'debugService',
    'debugSlice',
    'debugTags',
    'debugVisibilityViolations',
    'defer',
    'describeService',
    'diffGraph',
    'env',
    'exportMetrics',
    'exportGraph',
    'extend',
    'flush',
    'flushCompiled',
    'forContext',
    'forSlice',
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
    'showOwner',
    'showSlice',
    'singleton',
    'tag',
    'tagged',
    'validate',
    'whatBreaksIf',
    'warmCompiled',
    'when',
    'whoUses',
    'why',
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
