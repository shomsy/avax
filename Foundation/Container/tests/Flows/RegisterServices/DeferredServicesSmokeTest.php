<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

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

$container->singleton(DeferredRegularService::class, DeferredRegularService::class);
$container->defer(DeferredWorkerService::class, DeferredWorkerService::class);
$container->warmCompiled();

$regular = $container->get(DeferredRegularService::class);
$deferred = $container->get(DeferredWorkerService::class);
$regularDescription = $container->describeService(DeferredRegularService::class);
$deferredDescription = $container->describeService(DeferredWorkerService::class);

assertSame('regular', $regular->id(), 'Regular services should still resolve after warmup.');
assertSame('deferred', $deferred->id(), 'Deferred services should still resolve on demand.');
assertTrue($regularDescription['compiled'], 'Non-deferred warmable services should be compiled.');
assertTrue(! $deferredDescription['compiled'], 'Deferred services should stay out of the default compile path.');

echo basename(__FILE__) . " ok\n";
