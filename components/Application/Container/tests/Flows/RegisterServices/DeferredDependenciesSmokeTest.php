<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

final class DeferredRegularService
{
    public function id() : string
    {
        return 'regular';
    }
}

final class DeferredWorkerService
{
    public function id() : string
    {
        return 'deferred';
    }
}

$container = makeTestContainer();

$container->singleton(abstract: DeferredRegularService::class, concrete: DeferredRegularService::class);
$container->defer(abstract: DeferredWorkerService::class, concrete: DeferredWorkerService::class);
$container->warmCompiled();

$regular            = $container->get(id: DeferredRegularService::class);
$deferred           = $container->get(id: DeferredWorkerService::class);
$regularDescription = $container->describeService(id: DeferredRegularService::class);
$deferredDescription = $container->describeService(id: DeferredWorkerService::class);

assertSame(expected: 'regular', actual: $regular->id(), message: 'Regular services should still resolve after warmup.');
assertSame(expected: 'deferred', actual: $deferred->id(), message: 'Deferred services should still resolve on demand.');
assertTrue(condition: $regularDescription['compiled'], message: 'Non-deferred warmable services should be compiled.');
assertTrue(condition: ! $deferredDescription['compiled'], message: 'Deferred services should stay out of the default compile path.');

echo basename(path: __FILE__) . " ok\n";
