<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

interface LifecycleContract
{
    public function id(): string;
}

final class LifecycleService implements LifecycleContract
{
    #[Override]
    public function id(): string
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
$version  = 'lifecycle-smoke';
$config   = CreateContainerConfig::create(cacheDir: $cacheDir, cacheVersion: $version);
$artifact = $cacheDir . '/container/' . rawurlencode(string: $version) . '/compiled/container.php';

$container = makeTestContainer(config: $config);
$container->singleton(abstract: LifecycleContract::class, concrete: LifecycleService::class);
$container->scoped(abstract: LifecycleScopedService::class, concrete: LifecycleScopedService::class);
$container->openScope();

$shared = $container->get(id: LifecycleContract::class);
$scoped = $container->get(id: LifecycleScopedService::class);
$container->compileContainer(serviceIds: [LifecycleContract::class, LifecycleScopedService::class]);

assertSame(expected: 'lifecycle', actual: $shared->id(), message: 'Shared registrations should resolve before flush.');
assertSame(expected: 'scoped', actual: $scoped->name, message: 'Scoped registrations should resolve before flush.');
assertTrue(condition: is_file(filename: $artifact), message: 'Compiled runtime artifacts should be written before flush.');
assertTrue(condition: $container->debugScope()['shared'] !== [], message: 'Shared runtime storage should be populated before flush.');
assertTrue(condition: $container->debugScope()['scoped'] !== [], message: 'Scoped runtime storage should be populated before flush.');

$container->flush();

assertTrue(condition: ! is_file(filename: $artifact), message: 'Flush should remove the compiled runtime artifact.');
assertSame(expected: [], actual: $container->debugScope()['shared'], message: 'Flush should clear shared runtime storage.');
assertSame(expected: [], actual: $container->debugScope()['scoped'], message: 'Flush should clear scoped runtime storage.');
assertSame(expected: 'lifecycle', actual: $container->get(id: LifecycleContract::class)->id(), message: 'Flush should preserve canonical registrations.');
$container->openScope();
assertSame(expected: 'scoped', actual: $container->get(id: LifecycleScopedService::class)->name, message: 'Flush should preserve scoped registrations.');
$container->closeScope();

$container->compileContainer(serviceIds: [LifecycleContract::class, LifecycleScopedService::class]);
$container->get(id: LifecycleContract::class);
$container->openScope();
$container->get(id: LifecycleScopedService::class);
$container->reset();

assertTrue(condition: is_file(filename: $artifact), message: 'Reset should preserve compiled artifacts on disk.');
assertSame(expected: [], actual: $container->debugScope()['shared'], message: 'Reset should also leave shared runtime storage empty.');
assertSame(expected: [], actual: $container->debugScope()['scoped'], message: 'Reset should also leave scoped runtime storage empty.');
assertSame(expected: 'lifecycle', actual: $container->get(id: LifecycleContract::class)->id(), message: 'Reset should preserve canonical registrations.');

echo basename(path: __FILE__) . " ok\n";
