<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Execution\Injection\Attributes\RuntimeInput;
use Avax\Container\DI\ContainerInterface;

interface DecoratedContract
{
    public function label() : string;
}

final class DecoratedService implements DecoratedContract
{
    public bool $decorated = false;

    public function label() : string
    {
        return 'decorated';
    }
}

final class OwnedDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ContainerInterface|null $container = null) : mixed
    {
        assertInstanceOf(DecoratedService::class, $instance, 'Decorators should receive the resolved concrete instance.');
        $instance->decorated = true;

        return $instance;
    }
}

final class InvisibleDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ContainerInterface|null $container = null) : mixed
    {
        return $instance;
    }
}

final class RuntimeInputConsumer
{
    public function __construct(
        public DecoratedContract              $service,
        #[RuntimeInput('name')] public string $name
    ) {}
}

final class FirstGroupedStep
{
    public function name() : string
    {
        return 'first';
    }
}

final class SecondGroupedStep
{
    public function name() : string
    {
        return 'second';
    }
}

final class ConflictingGroupedStep
{
    public function name() : string
    {
        return 'conflict';
    }
}

$container = makeTestContainer();
$container->singleton(DecoratedContract::class, DecoratedService::class)
    ->asCapability('billing')
    ->asShared()
    ->export();
$container->singleton(OwnedDecorator::class, OwnedDecorator::class)
    ->asCapability('billing')
    ->asShared()
    ->export();
$container->decorate(DecoratedContract::class, OwnedDecorator::class);
$container->bind(RuntimeInputConsumer::class, RuntimeInputConsumer::class)
    ->asFlow('checkout')
    ->asPrivate()
    ->entry()
    ->import(['billing']);
$container->singleton(FirstGroupedStep::class, FirstGroupedStep::class)
    ->asCapability('checkout.pipeline')
    ->asShared()
    ->export()
    ->group('checkout.steps', 20);
$container->singleton(SecondGroupedStep::class, SecondGroupedStep::class)
    ->asCapability('checkout.pipeline')
    ->asShared()
    ->export()
    ->group('checkout.steps', 10);

$runtimeFactory  = $container->factory(RuntimeInputConsumer::class);
$consumer        = $container->make(RuntimeInputConsumer::class, ['name' => 'Ada']);
$factoryConsumer = $runtimeFactory(['name' => 'Grace']);
$description     = $container->describeService(DecoratedContract::class);
$grouped         = $container->grouped('checkout.steps');
$graph           = $container->debugGraph();
$groupReport     = $container->debugGroup('checkout.steps');
$selectionReport = $container->debugSelection(DecoratedContract::class);

assertSame('Ada', $consumer->name, 'Runtime input should come from explicit caller overrides.');
assertSame('Grace', $factoryConsumer->name, 'Factory closures should resolve the same authored selection path with caller overrides.');
assertTrue($consumer->service instanceof DecoratedService && $consumer->service->decorated, 'Decorators should still run before runtime-input consumers receive the service.');
assertSame(
    [SecondGroupedStep::class, FirstGroupedStep::class],
    array_map(static fn (object $service) : string => $service::class, $grouped),
    'Grouped multi-bindings should resolve in deterministic order.'
);
assertSame(
    [SecondGroupedStep::class, FirstGroupedStep::class],
    array_column($graph['groups']['checkout.steps'], 'serviceId'),
    'Graph diagnostics should expose grouped binding order.'
);
assertSame(
    [SecondGroupedStep::class, FirstGroupedStep::class],
    array_column($groupReport['items'], 'serviceId'),
    'Group diagnostics should expose grouped binding order.'
);
assertSame(OwnedDecorator::class, $description['decorationDetails'][0]['descriptor'] ?? null, 'Decorator diagnostics should expose the registered decorator descriptor.');
assertSame('billing', $description['decorationDetails'][0]['owner'] ?? null, 'Decorator diagnostics should expose the decorator owner slice.');
assertSame(DecoratedContract::class, $selectionReport['service'] ?? null, 'Selection diagnostics should expose the selected service id.');
assertSame(OwnedDecorator::class, $selectionReport['decorators'][0]['descriptor'] ?? null, 'Selection diagnostics should expose decorator wiring.');
assertTrue(
    ($description['decorationDetails'][0]['access']['allowed'] ?? false) === true,
    'Decorator diagnostics should explain that visible decorators are allowed from the owning slice.'
);

try {
    $container->make(RuntimeInputConsumer::class);
    throw new RuntimeException('Missing runtime input should fail.');
} catch (ContainerException $exception) {
    assertTrue(
        str_contains($exception->getMessage(), 'Runtime input [$name] is missing'),
        'Runtime input failures should name the missing input.'
    );
    assertTrue(
        str_contains($exception->getMessage(), 'Likely fix: pass an explicit override, use forContext(), or add a default value.'),
        'Runtime input failures should be fix-oriented.'
    );
}

$container->singleton(ConflictingGroupedStep::class, ConflictingGroupedStep::class)
    ->asCapability('checkout.pipeline')
    ->asShared()
    ->export()
    ->group('checkout.steps', 10);

$groupIssues = implode("\n", $container->validate([
                                                      FirstGroupedStep::class,
                                                      SecondGroupedStep::class,
                                                      ConflictingGroupedStep::class,
                                                  ]));

assertTrue(
    str_contains($groupIssues, 'Group [checkout.steps] uses duplicate order [10]'),
    'Validation should reject conflicting grouped binding order.'
);

$invisibleDecoratorContainer = makeTestContainer();
$invisibleDecoratorContainer->singleton(DecoratedContract::class, DecoratedService::class)
    ->asCapability('billing')
    ->asShared()
    ->export();
$invisibleDecoratorContainer->singleton(InvisibleDecorator::class, InvisibleDecorator::class)
    ->asCapability('audit')
    ->asInternal();
$invisibleDecoratorContainer->decorate(DecoratedContract::class, InvisibleDecorator::class);

$decoratorIssues = implode("\n", $invisibleDecoratorContainer->validate([DecoratedContract::class]));

assertTrue(
    str_contains($decoratorIssues, 'decorator is not visible from the service slice'),
    'Validation should reject decorators that violate slice visibility.'
);

echo basename(__FILE__) . " ok\n";
