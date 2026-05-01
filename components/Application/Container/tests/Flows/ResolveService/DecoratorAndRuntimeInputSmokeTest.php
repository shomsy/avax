<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Attributes\RuntimeInput;
use Avax\Components\Application\Container\DI\ContainerInterface;

interface DecoratedContract
{
    public function label(): string;
}

final class DecoratedService implements DecoratedContract
{
    public bool $decorated = false;

    #[Override]
    public function label(): string
    {
        return 'decorated';
    }
}

final class OwnedDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ?ContainerInterface $container = null): mixed
    {
        assertInstanceOf(expectedClass: DecoratedService::class, value: $instance, message: 'Decorators should receive the resolved concrete instance.');
        $instance->decorated = true;

        return $instance;
    }
}

final class InvisibleDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ?ContainerInterface $container = null): mixed
    {
        return $instance;
    }
}

final class RuntimeInputConsumer
{
    public function __construct(public DecoratedContract $decoratedContract, #[RuntimeInput(name: 'name')] public string $name)
    {
    }
}

final class FirstGroupedStep
{
    public function name(): string
    {
        return 'first';
    }
}

final class SecondGroupedStep
{
    public function name(): string
    {
        return 'second';
    }
}

final class ConflictingGroupedStep
{
    public function name(): string
    {
        return 'conflict';
    }
}

$container = makeTestContainer();
$container->singleton(abstract: DecoratedContract::class, concrete: DecoratedService::class)
    ->asCapability(ownerSlice: 'billing')
    ->asShared()
    ->export();
$container->singleton(abstract: OwnedDecorator::class, concrete: OwnedDecorator::class)
    ->asCapability(ownerSlice: 'billing')
    ->asShared()
    ->export();
$container->decorate(abstract: DecoratedContract::class, decorator: OwnedDecorator::class);
$container->bind(abstract: RuntimeInputConsumer::class, concrete: RuntimeInputConsumer::class)
    ->asFlow(ownerSlice: 'checkout')
    ->asPrivate()
    ->entry()
    ->import(slices: ['billing']);
$container->singleton(abstract: FirstGroupedStep::class, concrete: FirstGroupedStep::class)
    ->asCapability(ownerSlice: 'checkout.pipeline')
    ->asShared()
    ->export()
    ->group(group: 'checkout.steps', order: 20);
$container->singleton(abstract: SecondGroupedStep::class, concrete: SecondGroupedStep::class)
    ->asCapability(ownerSlice: 'checkout.pipeline')
    ->asShared()
    ->export()
    ->group(group: 'checkout.steps', order: 10);

$runtimeFactory  = $container->factory(abstract: RuntimeInputConsumer::class);
$consumer        = $container->make(abstract: RuntimeInputConsumer::class, parameters: ['name' => 'Ada']);
$factoryConsumer = $runtimeFactory(['name' => 'Grace']);
$description     = $container->describeService(id: DecoratedContract::class);
$grouped         = $container->grouped(group: 'checkout.steps');
$graph           = $container->debugGraph();
$groupReport     = $container->debugGroup(group: 'checkout.steps');
$selectionReport = $container->debugSelection(id: DecoratedContract::class);

assertSame(expected: 'Ada', actual: $consumer->name, message: 'Runtime input should come from explicit caller overrides.');
assertSame(expected: 'Grace', actual: $factoryConsumer->name, message: 'Factory closures should resolve the same authored selection path with caller overrides.');
assertTrue(condition: $consumer->service instanceof DecoratedService && $consumer->service->decorated, message: 'Decorators should still run before runtime-input consumers receive the service.');
assertSame(
    expected: [SecondGroupedStep::class, FirstGroupedStep::class],
    actual  : array_map(callback: static fn (object $service): string => $service::class, array: $grouped),
    message : 'Grouped multi-bindings should resolve in deterministic order.',
);
assertSame(
    expected: [SecondGroupedStep::class, FirstGroupedStep::class],
    actual  : array_column(array: $graph['groups']['checkout.steps'], column_key: 'serviceId'),
    message : 'Graph diagnostics should expose grouped binding order.',
);
assertSame(
    expected: [SecondGroupedStep::class, FirstGroupedStep::class],
    actual  : array_column(array: $groupReport['items'], column_key: 'serviceId'),
    message : 'Group diagnostics should expose grouped binding order.',
);
assertSame(expected: OwnedDecorator::class, actual: $description['decorationDetails'][0]['descriptor'] ?? null, message: 'Decorator diagnostics should expose the registered decorator descriptor.');
assertSame(expected: 'billing', actual: $description['decorationDetails'][0]['owner'] ?? null, message: 'Decorator diagnostics should expose the decorator owner slice.');
assertSame(expected: DecoratedContract::class, actual: $selectionReport['service'] ?? null, message: 'Selection diagnostics should expose the selected service id.');
assertSame(expected: OwnedDecorator::class, actual: $selectionReport['decorators'][0]['descriptor'] ?? null, message: 'Selection diagnostics should expose decorator wiring.');
assertTrue(
    condition: ($description['decorationDetails'][0]['access']['allowed'] ?? false) === true,
    message  : 'Decorator diagnostics should explain that visible decorators are allowed from the owning slice.',
);

try {
    $container->make(abstract: RuntimeInputConsumer::class);

    throw new RuntimeException(message: 'Missing runtime input should fail.');
} catch (ContainerException $containerException) {
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Runtime input [$name] is missing'),
        message  : 'Runtime input failures should name the missing input.',
    );
    assertTrue(
        condition: str_contains(haystack: $containerException->getMessage(), needle: 'Likely fix: pass an explicit override, use forContext(), or add a default value.'),
        message  : 'Runtime input failures should be fix-oriented.',
    );
}

$container->singleton(abstract: ConflictingGroupedStep::class, concrete: ConflictingGroupedStep::class)
    ->asCapability(ownerSlice: 'checkout.pipeline')
    ->asShared()
    ->export()
    ->group(group: 'checkout.steps', order: 10);

$groupIssues = implode(separator: "\n", array: $container->validate(serviceIds: [
                                                                                    FirstGroupedStep::class,
                                                                                    SecondGroupedStep::class,
                                                                                    ConflictingGroupedStep::class,
                                                                                ]));

assertTrue(
    condition: str_contains(haystack: $groupIssues, needle: 'Group [checkout.steps] uses duplicate order [10]'),
    message  : 'Validation should reject conflicting grouped binding order.',
);

$invisibleDecoratorContainer = makeTestContainer();
$invisibleDecoratorContainer->singleton(abstract: DecoratedContract::class, concrete: DecoratedService::class)
    ->asCapability(ownerSlice: 'billing')
    ->asShared()
    ->export();
$invisibleDecoratorContainer->singleton(abstract: InvisibleDecorator::class, concrete: InvisibleDecorator::class)
    ->asCapability(ownerSlice: 'audit')
    ->asInternal();
$invisibleDecoratorContainer->decorate(abstract: DecoratedContract::class, decorator: InvisibleDecorator::class);

$decoratorIssues = implode(separator: "\n", array: $invisibleDecoratorContainer->validate(serviceIds: [DecoratedContract::class]));

assertTrue(
    condition: str_contains(haystack: $decoratorIssues, needle: 'decorator is not visible from the service slice'),
    message  : 'Validation should reject decorators that violate slice visibility.',
);

echo basename(path: __FILE__) . " ok\n";
