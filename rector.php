<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(paths: [
        __DIR__.'/Foundation/Container',
    ]);

    $rectorConfig->phpVersion(phpVersion: PhpVersion::PHP_83);
    $rectorConfig->sets(sets: [
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::STRICT_BOOLEANS,
        SetList::PHP_83,
    ]);

    // Namespace refactor mappings for DSL evolution
    $rectorConfig->ruleWithConfiguration(rectorClass: RenameNamespaceRector::class, configuration: [
        // Top-level namespace changes
        'Avax\Container\Application' => 'Avax\Container\Operate\Boot',
        'Avax\Container\Analysis' => 'Avax\Container\Think',
        'Avax\Container\Attributes' => 'Avax\Container\Core\Attribute',
        'Avax\Container\Behavior' => 'Avax\Container\Operate',
        'Avax\Container\Builder' => 'Avax\Container\Operate\Boot',
        'Avax\Container\Console' => 'Avax\Container\Tools\Console',
        'Avax\Container\Diagnostics' => 'Avax\Container\Observe',
        'Avax\Container\Execution' => 'Avax\Container\Act',
        'Avax\Container\Kernel' => 'Avax\Container\Operate\Boot',
        'Avax\Container\Lifecycle' => 'Avax\Container\Operate',
        'Avax\Container\Planning' => 'Avax\Container\Think',
        'Avax\Container\Policy' => 'Avax\Container\Guard',
        'Avax\Container\Read' => 'Avax\Container\Act',
        'Avax\Container\Runtime' => 'Avax\Container\Act',
        'Avax\Container\Security' => 'Avax\Container\Guard',
        'Avax\Container\Services' => 'Avax\Container\Define',
        'Avax\Container\Support' => 'Avax\Container\Core',
        'Avax\Container\Validate' => 'Avax\Container\Guard',
        'Avax\Container\Write' => 'Avax\Container\Define',

        // Sub-namespace refinements
        'Avax\Container\Runtime\Build' => 'Avax\Container\Act\Invoke',
        'Avax\Container\Runtime\Engine' => 'Avax\Container\Act\Resolve',
        'Avax\Container\Runtime\Resolve' => 'Avax\Container\Act\Resolve',
        'Avax\Container\Planning\MakePlan' => 'Avax\Container\Think\Prototype',
        'Avax\Container\Planning\Cache' => 'Avax\Container\Think\Cache',
        'Avax\Container\Diagnostics\Telemetry' => 'Avax\Container\Observe\Metrics',
        'Avax\Container\Diagnostics\Debug' => 'Avax\Container\Observe\Inspect',
        'Avax\Container\Policy\Security' => 'Avax\Container\Guard\Enforce',
        'Avax\Container\Behavior\Lifecycle' => 'Avax\Container\Operate\Boot',
        'Avax\Container\Behavior\Policy' => 'Avax\Container\Guard\Rules',
        'Avax\Container\Read\DependencyInjector' => 'Avax\Container\Act\Inject',
        'Avax\Container\Read\Resolver' => 'Avax\Container\Act\Resolve',
        'Avax\Container\Read\Traits' => 'Avax\Container\Act\Lazy',
        'Avax\Container\Write\KeepIn' => 'Avax\Container\Define\Store',
        'Avax\Container\Services\Definition' => 'Avax\Container\Define\Store',
        'Avax\Container\Services\Lifecycle' => 'Avax\Container\Operate\Boot',
        'Avax\Container\Validate\Telemetry' => 'Avax\Container\Observe\Metrics',
        'Avax\Container\Execution\Proxy' => 'Avax\Container\Act\Lazy',
        'Avax\Container\Execution\Invoker' => 'Avax\Container\Act\Invoke',
        'Avax\Container\Analysis\Dumper' => 'Avax\Container\Think\Prototype',
        'Avax\Container\Analysis\Cache' => 'Avax\Container\Think\Cache',
        'Avax\Container\Application\Provider' => 'Avax\Container\Operate\Boot',
        'Avax\Container\Kernel\Bootstrap' => 'Avax\Container\Operate\Boot',
    ]);
};
