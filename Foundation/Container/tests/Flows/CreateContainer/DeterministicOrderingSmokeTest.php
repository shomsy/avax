<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Container;
use Avax\Container\DI\ContainerInterface;
use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Container\DI\Capabilities\Declaration\Providers\ProviderBootPlan;
use Avax\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;

final class OrderingProviderAlpha implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [];
    }

    public function register() : void
    {
    }

    public function boot() : void
    {
    }
}

final class OrderingProviderBeta implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [OrderingProviderAlpha::class];
    }

    public function register() : void
    {
    }

    public function boot() : void
    {
    }
}

final class OrderingProviderGamma implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [OrderingProviderAlpha::class];
    }

    public function register() : void
    {
    }

    public function boot() : void
    {
    }
}

final class OrderingTaggedA
{
}

final class OrderingTaggedB
{
}

final class OrderingTaggedC
{
}

final class OrderingDecoratedService
{
    public function __construct(public string $value = 'base')
    {
    }
}

final class OrderingFirstDecorator
{
    public function __construct(public OrderingDecoratedService $inner)
    {
    }
}

final class OrderingSecondDecorator
{
    public function __construct(public OrderingFirstDecorator $inner)
    {
    }
}

final class OrderingArtifactDependency
{
}

final class OrderingArtifactService
{
    public function __construct(public OrderingArtifactDependency $dependency)
    {
    }
}

/**
 * @return array<string, mixed>
 */
function normalizedArtifactMetadata(Container $container) : array
{
    $report = $container->compileReport([OrderingArtifactService::class, OrderingArtifactDependency::class]);
    assertTrue($report !== null && $report->metadata !== null, 'Compile report metadata should exist for ordering proofs.');

    $metadata = $report->metadata->toArray();
    unset($metadata['compiledAt'], $metadata['artifactPaths']);

    return $metadata;
}

$providerPlan = ProviderBootPlan::build([
    OrderingProviderGamma::class => new OrderingProviderGamma(makeTestContainer()),
    OrderingProviderAlpha::class => new OrderingProviderAlpha(makeTestContainer()),
    OrderingProviderBeta::class => new OrderingProviderBeta(makeTestContainer()),
]);

assertSame(
    [OrderingProviderAlpha::class, OrderingProviderBeta::class, OrderingProviderGamma::class],
    $providerPlan->order,
    'Provider boot order must stay deterministic and dependency-aware.'
);

$registry = new ServiceRegistry;
$registry->bind(OrderingTaggedC::class, OrderingTaggedC::class)->tag('ordered');
$registry->bind(OrderingTaggedA::class, OrderingTaggedA::class)->tag('ordered');
$registry->bind(OrderingTaggedB::class, OrderingTaggedB::class)->tag('ordered');
$registry->bind(OrderingDecoratedService::class, OrderingDecoratedService::class);
$registry->decorate(OrderingDecoratedService::class, OrderingFirstDecorator::class);
$registry->decorate(OrderingDecoratedService::class, OrderingSecondDecorator::class);

assertSame(
    [OrderingTaggedA::class, OrderingTaggedB::class, OrderingTaggedC::class],
    $registry->getTaggedIds('ordered'),
    'Tag ordering must stay deterministic regardless of registration order.'
);
assertSame(
    [OrderingFirstDecorator::class, OrderingSecondDecorator::class],
    $registry->decorationChain(OrderingDecoratedService::class),
    'Decoration ordering must stay deterministic and preserve explicit registration order.'
);

$leftCache = sys_get_temp_dir() . '/container-ordering-left-' . uniqid();
$rightCache = sys_get_temp_dir() . '/container-ordering-right-' . uniqid();

$left = makeTestContainer(CreateContainerConfig::create(cacheDir: $leftCache, cacheVersion: 'ordering-proof'));
$left->singleton(OrderingArtifactDependency::class, OrderingArtifactDependency::class);
$left->singleton(OrderingArtifactService::class, OrderingArtifactService::class);
$left->alias('ordering.alias', OrderingArtifactService::class);
$left->tag([OrderingArtifactService::class, OrderingArtifactDependency::class], 'ordered-artifact');
$left->decorate(OrderingArtifactService::class, OrderingFirstDecorator::class);
$left->decorate(OrderingArtifactService::class, OrderingSecondDecorator::class);
$left->compileContainer([OrderingArtifactService::class, OrderingArtifactDependency::class]);

$right = makeTestContainer(CreateContainerConfig::create(cacheDir: $rightCache, cacheVersion: 'ordering-proof'));
$right->singleton(OrderingArtifactService::class, OrderingArtifactService::class);
$right->singleton(OrderingArtifactDependency::class, OrderingArtifactDependency::class);
$right->decorate(OrderingArtifactService::class, OrderingFirstDecorator::class);
$right->decorate(OrderingArtifactService::class, OrderingSecondDecorator::class);
$right->tag([OrderingArtifactDependency::class, OrderingArtifactService::class], 'ordered-artifact');
$right->alias('ordering.alias', OrderingArtifactService::class);
$right->compileContainer([OrderingArtifactDependency::class, OrderingArtifactService::class]);

assertSame(
    normalizedArtifactMetadata($left),
    normalizedArtifactMetadata($right),
    'Compiled artifact metadata ordering must stay deterministic across equivalent registration orderings.'
);

echo basename(__FILE__) . " ok\n";
