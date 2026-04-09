<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

interface CompiledGreeterContract
{
    public function message() : string;
}

final class CompiledGreeter implements CompiledGreeterContract
{
    public function message() : string
    {
        return 'compiled-runtime';
    }
}

final class CompiledNeedsGreeter
{
    public function __construct(public CompiledGreeterContract $greeter) {}
}

final class CompiledConfiguredMessage
{
    public function __construct(public string $name) {}
}

final class CompiledNeedsObjectArgument
{
    public function __construct(public stdClass $payload) {}
}

$cacheDir = sys_get_temp_dir() . '/container-runtime-' . uniqid();
$version  = 'compiled-container-smoke';
$config   = CreateContainerConfig::create(cacheDir: $cacheDir, cacheVersion: $version);
$artifact = $cacheDir . '/container/' . rawurlencode($version) . '/compiled/container.php';

$first = makeTestContainer($config);
$first->bind(CompiledGreeterContract::class, CompiledGreeter::class);
$first->singleton(CompiledConfiguredMessage::class, CompiledConfiguredMessage::class)->withArgument('name', 'from-compiled');
$first->singleton(CompiledNeedsObjectArgument::class, CompiledNeedsObjectArgument::class)
    ->withArgument('payload', (object) ['kind' => 'dynamic-fallback']);
$first->compileContainer([CompiledNeedsGreeter::class, CompiledConfiguredMessage::class, CompiledNeedsObjectArgument::class]);

assertTrue(is_file($artifact), 'CompileContainer should write a generated compiled runtime artifact.');

$resolvedFromCompiled       = $first->get(CompiledNeedsGreeter::class);
$configuredFromCompiled     = $first->get(CompiledConfiguredMessage::class);
$objectArgumentFromCompiled = $first->get(CompiledNeedsObjectArgument::class);
assertSame('compiled-runtime', $resolvedFromCompiled->greeter->message(), 'Compiled runtime should resolve bound dependencies.');
assertSame('from-compiled', $configuredFromCompiled->name, 'Compiled runtime should preserve registration constructor arguments.');
assertSame('dynamic-fallback', $objectArgumentFromCompiled->payload->kind, 'Non-compile-safe constructor arguments should still resolve through the dynamic fallback path.');
assertTrue(
    str_contains($first->exportMetrics(), 'container_compiled_container_resolve_total'),
    'Compiled runtime should record hot-path resolution metrics.'
);

$second = makeTestContainer($config);
$second->bind(CompiledGreeterContract::class, CompiledGreeter::class);
$second->singleton(CompiledConfiguredMessage::class, CompiledConfiguredMessage::class)->withArgument('name', 'from-compiled');
$second->singleton(CompiledNeedsObjectArgument::class, CompiledNeedsObjectArgument::class)
    ->withArgument('payload', (object) ['kind' => 'dynamic-fallback']);
$resolvedFromDisk       = $second->get(CompiledNeedsGreeter::class);
$configuredFromDisk     = $second->get(CompiledConfiguredMessage::class);
$objectArgumentFromDisk = $second->get(CompiledNeedsObjectArgument::class);
$metrics                = $second->exportMetrics();

assertSame('compiled-runtime', $resolvedFromDisk->greeter->message(), 'A second container should load the compiled runtime from disk.');
assertSame('from-compiled', $configuredFromDisk->name, 'Disk-loaded compiled runtime should preserve registration constructor arguments.');
assertSame('dynamic-fallback', $objectArgumentFromDisk->payload->kind, 'Disk-loaded runtime should preserve dynamic fallback services with object arguments.');
assertTrue(
    str_contains($metrics, 'container_compiled_container_hits_total'),
    'Loading a generated runtime from disk should be reported.'
);

echo basename(__FILE__) . " ok\n";
