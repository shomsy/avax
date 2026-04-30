<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Container;
use Avax\Components\Application\Container\DI\ContainerInterface;

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
    'debugArchitecture',
    'debugExports',
    'debugGraph',
    'debugGovernance',
    'debugGroup',
    'debugImports',
    'debugPlan',
    'debugSelection',
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
    'factory',
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
    assertTrue(condition: method_exists(object_or_class: ContainerInterface::class, method: $method), message: sprintf('ContainerInterface should expose [%s].', $method));
    assertTrue(condition: method_exists(object_or_class: Container::class, method: $method), message: sprintf('Container should implement [%s].', $method));
}

$forbiddenLegacyMethods = [
    'beginScope',
    'endScope',
];

foreach ($forbiddenLegacyMethods as $forbiddenLegacyMethod) {
    assertTrue(condition: ! method_exists(object_or_class: ContainerInterface::class, method: $forbiddenLegacyMethod), message: sprintf('Legacy public method [%s] must stay absent from ContainerInterface.', $forbiddenLegacyMethod));
    assertTrue(condition: ! method_exists(object_or_class: Container::class, method: $forbiddenLegacyMethod), message: sprintf('Legacy public method [%s] must stay absent from Container.', $forbiddenLegacyMethod));
}

echo basename(path: __FILE__) . " ok\n";
