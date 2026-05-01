<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\ProviderBootPlan;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\DI\Container;

final class OrderingProviderAlpha implements RegisterDependency
{
    public function dependsOn() : array
    {
        return [];
    }
}

final class OrderingProviderBeta implements RegisterDependency
{
    public function dependsOn() : array
    {
        return [OrderingProviderAlpha::class];
    }
}

final class OrderingProviderGamma implements RegisterDependency
{
    public function dependsOn() : array
    {
        return [OrderingProviderAlpha::class];
    }
}

final class OrderingTaggedA {}

final class OrderingTaggedB {}

final class OrderingTaggedC {}

final class OrderingDecoratedService
{
    public function __construct(public string $value = 'base') {}
}

final class OrderingFirstDecorator
{
    public function __construct(public OrderingDecoratedService $orderingDecoratedService) {}
}

final class OrderingSecondDecorator
{
    public function __construct(public OrderingFirstDecorator $orderingFirstDecorator) {}
}

final class OrderingArtifactDependency {}

final class OrderingArtifactService
{
    public function __construct(public OrderingArtifactDependency $orderingArtifactDependency) {}
}

/**
 * @return array<string, mixed>
 */
function normalizedArtifactMetadata(Container $container) : array
{
    $report = $container->compileReport(serviceIds: [OrderingArtifactService::class, OrderingArtifactDependency::class]);
    assertTrue(condition: $report !== null && $report->metadata !== null, message: 'Compile report metadata should exist for ordering proofs.');

    $metadata = $report->metadata->toArray();
    unset($metadata['compiledAt'], $metadata['artifactPaths']);

    return $metadata;
}

$providerPlan = ProviderBootPlan::build(instances: [
                                                       OrderingProviderGamma::class => new OrderingProviderGamma(app: makeTestContainer()),
                                                       OrderingProviderAlpha::class => new OrderingProviderAlpha(app: makeTestContainer()),
                                                       OrderingProviderBeta::class  => new OrderingProviderBeta(app: makeTestContainer()),
                                                   ]);

assertSame(
    expected: [OrderingProviderAlpha::class, OrderingProviderBeta::class, OrderingProviderGamma::class],
    actual  : $providerPlan->order,
    message : 'Provider boot order must stay deterministic and dependency-aware.',
);

$registry = new DependencyRegistry;
$registry->bind(abstract: OrderingTaggedC::class, concrete: OrderingTaggedC::class)->tag(tags: 'ordered');
$registry->bind(abstract: OrderingTaggedA::class, concrete: OrderingTaggedA::class)->tag(tags: 'ordered');
$registry->bind(abstract: OrderingTaggedB::class, concrete: OrderingTaggedB::class)->tag(tags: 'ordered');
$registry->bind(abstract: OrderingDecoratedService::class, concrete: OrderingDecoratedService::class);
$registry->decorate(abstract: OrderingDecoratedService::class, decorator: OrderingFirstDecorator::class);
$registry->decorate(abstract: OrderingDecoratedService::class, decorator: OrderingSecondDecorator::class);

assertSame(
    expected: [OrderingTaggedA::class, OrderingTaggedB::class, OrderingTaggedC::class],
    actual  : $registry->getTaggedIds(tag: 'ordered'),
    message : 'Tag ordering must stay deterministic regardless of registration order.',
);
assertSame(
    expected: [OrderingFirstDecorator::class, OrderingSecondDecorator::class],
    actual  : $registry->decorationChain(abstract: OrderingDecoratedService::class),
    message : 'Decoration ordering must stay deterministic and preserve explicit registration order.',
);

$leftCache  = sys_get_temp_dir() . '/container-ordering-left-' . uniqid();
$rightCache = sys_get_temp_dir() . '/container-ordering-right-' . uniqid();

$left = makeTestContainer(config: CreateContainerConfig::create(cacheDir: $leftCache, cacheVersion: 'ordering-proof'));
$left->singleton(abstract: OrderingArtifactDependency::class, concrete: OrderingArtifactDependency::class);
$left->singleton(abstract: OrderingArtifactService::class, concrete: OrderingArtifactService::class);
$left->alias(alias: 'ordering.alias', abstract: OrderingArtifactService::class);
$left->tag(abstracts: [OrderingArtifactService::class, OrderingArtifactDependency::class], tags: 'ordered-artifact');
$left->decorate(abstract: OrderingArtifactService::class, decorator: OrderingFirstDecorator::class);
$left->decorate(abstract: OrderingArtifactService::class, decorator: OrderingSecondDecorator::class);
$left->compileContainer(serviceIds: [OrderingArtifactService::class, OrderingArtifactDependency::class]);

$right = makeTestContainer(config: CreateContainerConfig::create(cacheDir: $rightCache, cacheVersion: 'ordering-proof'));
$right->singleton(abstract: OrderingArtifactService::class, concrete: OrderingArtifactService::class);
$right->singleton(abstract: OrderingArtifactDependency::class, concrete: OrderingArtifactDependency::class);
$right->decorate(abstract: OrderingArtifactService::class, decorator: OrderingFirstDecorator::class);
$right->decorate(abstract: OrderingArtifactService::class, decorator: OrderingSecondDecorator::class);
$right->tag(abstracts: [OrderingArtifactDependency::class, OrderingArtifactService::class], tags: 'ordered-artifact');
$right->alias(alias: 'ordering.alias', abstract: OrderingArtifactService::class);
$right->compileContainer(serviceIds: [OrderingArtifactDependency::class, OrderingArtifactService::class]);

assertSame(
    expected: normalizedArtifactMetadata(container: $left),
    actual  : normalizedArtifactMetadata(container: $right),
    message : 'Compiled artifact metadata ordering must stay deterministic across equivalent registration orderings.',
);

echo basename(path: __FILE__) . " ok\n";
