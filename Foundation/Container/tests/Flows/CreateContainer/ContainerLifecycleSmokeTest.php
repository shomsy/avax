<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ServiceNotFoundException;

interface LifecycleContract
{
    public function id() : string;
}

final class LifecycleService implements LifecycleContract
{
    public function id() : string
    {
        return 'lifecycle';
    }
}

final class LifecycleScopedService
{
    public function __construct(public string $name = 'scoped')
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-lifecycle-' . uniqid();
$version = 'lifecycle-smoke';
$config = CreateContainerConfig::create(cacheDir: $cacheDir, cacheVersion: $version);
$artifact = $cacheDir . '/container/' . rawurlencode($version) . '/compiled/container.php';

$container = makeTestContainer($config);
$container->singleton(LifecycleContract::class, LifecycleService::class);
$container->scoped(LifecycleScopedService::class, LifecycleScopedService::class);
$container->openScope();

$shared = $container->get(LifecycleContract::class);
$scoped = $container->get(LifecycleScopedService::class);
$container->compileContainer([LifecycleContract::class, LifecycleScopedService::class]);

assertSame('lifecycle', $shared->id(), 'Shared registrations should resolve before flush.');
assertSame('scoped', $scoped->name, 'Scoped registrations should resolve before flush.');
assertTrue(is_file($artifact), 'Compiled runtime artifacts should be written before flush.');
assertTrue($container->debugScope()['shared'] !== [], 'Shared runtime storage should be populated before flush.');
assertTrue($container->debugScope()['scoped'] !== [], 'Scoped runtime storage should be populated before flush.');

$container->flush();

assertTrue(! is_file($artifact), 'Flush should remove the compiled runtime artifact.');
assertSame([], $container->debugScope()['shared'], 'Flush should clear shared runtime storage.');
assertSame([], $container->debugScope()['scoped'], 'Flush should clear scoped runtime storage.');
assertSame('lifecycle', $container->get(LifecycleContract::class)->id(), 'Flush should preserve canonical registrations.');
$container->openScope();
assertSame('scoped', $container->get(LifecycleScopedService::class)->name, 'Flush should preserve scoped registrations.');
$container->closeScope();

$container->compileContainer([LifecycleContract::class, LifecycleScopedService::class]);
$container->get(LifecycleContract::class);
$container->openScope();
$container->get(LifecycleScopedService::class);
$container->reset();

assertTrue(is_file($artifact), 'Reset should preserve compiled artifacts on disk.');
assertSame([], $container->debugScope()['shared'], 'Reset should also leave shared runtime storage empty.');
assertSame([], $container->debugScope()['scoped'], 'Reset should also leave scoped runtime storage empty.');
assertSame('lifecycle', $container->get(LifecycleContract::class)->id(), 'Reset should preserve canonical registrations.');

echo basename(__FILE__) . " ok\n";
