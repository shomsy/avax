<?php

declare(strict_types=1);

use Avax\Tooling\Rector\NoForbiddenNamespaceDirRector\NoForbiddenNamespaceDirRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/framework',
        __DIR__.'/components',
        __DIR__.'/tooling',
        __DIR__.'/tests/Unit/Framework',
        __DIR__.'/tests/Feature/Framework',
        __DIR__.'/tests/Contract',
    ]);

    // Define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::STRICT_BOOLEANS,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::CODING_STYLE,
    ]);

    // Target PHP 8.5 explicitly
    $rectorConfig->phpVersion(80500);

    $rectorConfig->skip([
        // Dangerous: can break public API backwards compatibility
        RemoveUnusedPublicMethodParameterRector::class,
        // Dangerous: can remove parameters that are passed to methods with func_get_args() or planned future use
        RemoveExtraParametersRector::class,
        // Temporary: avoid PSR-11 interface redeclaration conflicts during autoload
        __DIR__.'/components/Application/Container/tests',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    // AvaX custom governance rules
    $rectorConfig->rule(NoForbiddenNamespaceDirRector::class);

    $rectorConfig->disableParallel();
};
