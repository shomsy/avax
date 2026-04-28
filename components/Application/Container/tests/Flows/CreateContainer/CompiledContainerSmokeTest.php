<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

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
    public CompiledGreeterContract $greeter;

    public function __construct(CompiledGreeterContract $greeter) { $this->greeter = $greeter; }
}

final class CompiledConfiguredMessage
{
    public string $name;

    public function __construct(string $name) { $this->name = $name; }
}

final class CompiledNeedsObjectArgument
{
    public stdClass $payload;

    public function __construct(stdClass $payload) { $this->payload = $payload; }
}

$cacheDir = sys_get_temp_dir() . '/container-runtime-' . uniqid();
$version  = 'compiled-container-smoke';
$config   = CreateContainerConfig::create(cacheDir: $cacheDir, cacheVersion: $version);
$artifact = $cacheDir . '/container/' . rawurlencode(string: $version) . '/compiled/container.php';

$first = makeTestContainer(config: $config);
$first->bind(abstract: CompiledGreeterContract::class, concrete: CompiledGreeter::class);
$first->singleton(abstract: CompiledConfiguredMessage::class, concrete: CompiledConfiguredMessage::class)->withArgument(name: 'name', value: 'from-compiled');
$first->singleton(abstract: CompiledNeedsObjectArgument::class, concrete: CompiledNeedsObjectArgument::class)
    ->withArgument(name: 'payload', value: (object) ['kind' => 'dynamic-fallback']);
$first->compileContainer(serviceIds: [CompiledNeedsGreeter::class, CompiledConfiguredMessage::class, CompiledNeedsObjectArgument::class]);

assertTrue(condition: is_file(filename: $artifact), message: 'CompileContainer should write a generated compiled runtime artifact.');

$resolvedFromCompiled       = $first->get(id: CompiledNeedsGreeter::class);
$configuredFromCompiled     = $first->get(id: CompiledConfiguredMessage::class);
$objectArgumentFromCompiled = $first->get(id: CompiledNeedsObjectArgument::class);
assertSame(expected: 'compiled-runtime', actual: $resolvedFromCompiled->greeter->message(), message: 'Compiled runtime should resolve bound dependencies.');
assertSame(expected: 'from-compiled', actual: $configuredFromCompiled->name, message: 'Compiled runtime should preserve registration constructor arguments.');
assertSame(expected: 'dynamic-fallback', actual: $objectArgumentFromCompiled->payload->kind, message: 'Non-compile-safe constructor arguments should still resolve through the dynamic fallback path.');
assertTrue(
    condition: str_contains(haystack: $first->exportMetrics(), needle: 'container_compiled_container_resolve_total'),
    message  : 'Compiled runtime should record hot-path resolution metrics.'
);

$second = makeTestContainer(config: $config);
$second->bind(abstract: CompiledGreeterContract::class, concrete: CompiledGreeter::class);
$second->singleton(abstract: CompiledConfiguredMessage::class, concrete: CompiledConfiguredMessage::class)->withArgument(name: 'name', value: 'from-compiled');
$second->singleton(abstract: CompiledNeedsObjectArgument::class, concrete: CompiledNeedsObjectArgument::class)
    ->withArgument(name: 'payload', value: (object) ['kind' => 'dynamic-fallback']);
$resolvedFromDisk       = $second->get(id: CompiledNeedsGreeter::class);
$configuredFromDisk     = $second->get(id: CompiledConfiguredMessage::class);
$objectArgumentFromDisk = $second->get(id: CompiledNeedsObjectArgument::class);
$metrics                = $second->exportMetrics();

assertSame(expected: 'compiled-runtime', actual: $resolvedFromDisk->greeter->message(), message: 'A second container should load the compiled runtime from disk.');
assertSame(expected: 'from-compiled', actual: $configuredFromDisk->name, message: 'Disk-loaded compiled runtime should preserve registration constructor arguments.');
assertSame(expected: 'dynamic-fallback', actual: $objectArgumentFromDisk->payload->kind, message: 'Disk-loaded runtime should preserve dynamic fallback services with object arguments.');
assertTrue(
    condition: str_contains(haystack: $metrics, needle: 'container_compiled_container_hits_total'),
    message  : 'Loading a generated runtime from disk should be reported.'
);

echo basename(path: __FILE__) . " ok\n";
