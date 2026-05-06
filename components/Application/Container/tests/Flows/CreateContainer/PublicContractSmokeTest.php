<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__).'/bootstrap.php';

use Avax\Components\Application\Container\System\Container;
use Avax\Components\Application\Container\System\ContainerInterface;

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

foreach ($expectedMethods as $expectedMethod) {
    assertTrue(condition: method_exists(object_or_class: ContainerInterface::class, method: $expectedMethod), message: sprintf('ContainerInterface should expose [%s].', $expectedMethod));
    assertTrue(condition: method_exists(object_or_class: Container::class, method: $expectedMethod), message: sprintf('Container should implement [%s].', $expectedMethod));
}

$forbiddenLegacyMethods = [
    'beginScope',
    'endScope',
];

foreach ($forbiddenLegacyMethods as $forbiddenLegacyMethod) {
    assertTrue(condition: ! method_exists(object_or_class: ContainerInterface::class, method: $forbiddenLegacyMethod), message: sprintf('Legacy public method [%s] must stay absent from ContainerInterface.', $forbiddenLegacyMethod));
    assertTrue(condition: ! method_exists(object_or_class: Container::class, method: $forbiddenLegacyMethod), message: sprintf('Legacy public method [%s] must stay absent from Container.', $forbiddenLegacyMethod));
}

echo basename(path: __FILE__)." ok\n";
