<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class WorkerSharedService
{
}

final class WorkerScopedService
{
}

$cacheDir = sys_get_temp_dir() . '/container-worker-lifecycle-' . uniqid();
$version = 'worker-lifecycle';
$container = makeTestContainer(CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION
));

$container->singleton(WorkerSharedService::class, WorkerSharedService::class);
$container->scoped(WorkerScopedService::class, WorkerScopedService::class);
$container->warmCompiled([WorkerSharedService::class, WorkerScopedService::class]);

$container->openScope();
$jobOneShared = $container->get(WorkerSharedService::class);
$jobOneScoped = $container->get(WorkerScopedService::class);
$container->closeScope();

$beforeReset = $container->runtimeReport();
assertSame(1, $beforeReset->sharedServiceCount, 'Worker lifecycle should expose shared runtime state before reset.');

$container->reset();

$afterReset = $container->runtimeReport();
assertTrue($afterReset->warmedUp, 'Worker reset should preserve compiled warmup state.');
assertSame(0, $afterReset->sharedServiceCount, 'Worker reset should clear shared runtime instances.');
assertSame(0, $afterReset->scopedServiceCount, 'Worker reset should clear scoped runtime instances.');

$container->openScope();
$jobTwoShared = $container->get(WorkerSharedService::class);
$jobTwoScoped = $container->get(WorkerScopedService::class);
$container->closeScope();

assertNotSame($jobOneShared, $jobTwoShared, 'Worker reset should rebuild shared instances for the next job.');
assertNotSame($jobOneScoped, $jobTwoScoped, 'Worker reset should rebuild scoped instances for the next job.');

$container->flush();

assertTrue(! $container->isWarmedUp(), 'Flush should drop compiled artifacts from the current worker lifecycle.');
assertInstanceOf(
    WorkerSharedService::class,
    $container->get(WorkerSharedService::class),
    'Flush should still preserve authored registrations across jobs.'
);

echo basename(__FILE__) . " ok\n";
