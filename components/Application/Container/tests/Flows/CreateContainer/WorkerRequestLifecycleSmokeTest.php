<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class WorkerSharedService {}

final class WorkerScopedService {}

$cacheDir  = sys_get_temp_dir() . '/container-worker-lifecycle-' . uniqid();
$version   = 'worker-lifecycle';
$container = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
));

$container->singleton(abstract: WorkerSharedService::class, concrete: WorkerSharedService::class);
$container->scoped(abstract: WorkerScopedService::class, concrete: WorkerScopedService::class);
$container->warmCompiled(serviceIds: [WorkerSharedService::class, WorkerScopedService::class]);

$container->openScope();
$jobOneShared = $container->get(id: WorkerSharedService::class);
$jobOneScoped = $container->get(id: WorkerScopedService::class);
$container->closeScope();

$beforeReset = $container->runtimeReport();
assertSame(expected: 1, actual: $beforeReset->sharedServiceCount, message: 'Worker lifecycle should expose shared runtime state before reset.');

$container->reset();

$afterReset = $container->runtimeReport();
assertTrue(condition: $afterReset->warmedUp, message: 'Worker reset should preserve compiled warmup state.');
assertSame(expected: 0, actual: $afterReset->sharedServiceCount, message: 'Worker reset should clear shared runtime instances.');
assertSame(expected: 0, actual: $afterReset->scopedServiceCount, message: 'Worker reset should clear scoped runtime instances.');

$container->openScope();
$jobTwoShared = $container->get(id: WorkerSharedService::class);
$jobTwoScoped = $container->get(id: WorkerScopedService::class);
$container->closeScope();

assertNotSame(expected: $jobOneShared, actual: $jobTwoShared, message: 'Worker reset should rebuild shared instances for the next job.');
assertNotSame(expected: $jobOneScoped, actual: $jobTwoScoped, message: 'Worker reset should rebuild scoped instances for the next job.');

$container->flush();

assertTrue(condition: ! $container->isWarmedUp(), message: 'Flush should drop compiled artifacts from the current worker lifecycle.');
assertInstanceOf(
    expectedClass: WorkerSharedService::class,
    value        : $container->get(id: WorkerSharedService::class),
    message      : 'Flush should still preserve authored registrations across jobs.',
);

echo basename(path: __FILE__) . " ok\n";
