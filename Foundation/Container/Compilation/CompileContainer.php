<?php

declare(strict_types=1);

namespace Avax\Container\Compilation;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\Observability\ResolutionMetrics;
use Closure;
use RuntimeException;

final class CompileContainer
{
    private ServiceCompiler $services;

    public function __construct(
        private readonly ServiceRegistry $registrations,
        private readonly CreateServiceBlueprint $blueprints,
        private readonly string $cacheDir = '',
        private readonly string $cacheVersion = 'container-v1',
        private readonly ResolutionMetrics|null $metrics = null,
        ServiceCompiler|null $services = null
    ) {
        $this->services = $services ?? new ServiceCompiler(
            registrations: $this->registrations,
            blueprints   : $this->blueprints
        );
    }

    /**
     * @param list<string> $serviceIds
     */
    public function compile(array $serviceIds = []) : CompiledContainer
    {
        $snapshot = $this->snapshot(serviceIds: $serviceIds);
        $source = $this->sourceFor(snapshot: $snapshot);
        $compiled = $this->loadSource(source: $source, fingerprint: $snapshot['fingerprint']);

        if ($this->cacheDir !== '') {
            $this->write(source: $source);
            $this->metrics?->increment(name: 'container_compiled_container_writes_total');
        } else {
            $this->metrics?->increment(name: 'container_compiled_container_builds_total');
        }

        return $compiled;
    }

    /**
     * @param list<string> $serviceIds
     */
    public function load(array $serviceIds = []) : CompiledContainer|null
    {
        if ($this->cacheDir === '') {
            return null;
        }

        $path = $this->path();
        if (! is_file($path)) {
            $this->metrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        $snapshot = $this->snapshot(serviceIds: $serviceIds);
        $loaded = require $path;
        if (! $loaded instanceof CompiledContainer || $loaded->fingerprint() !== $snapshot['fingerprint']) {
            $this->metrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        $this->metrics?->increment(name: 'container_compiled_container_hits_total');

        return $loaded;
    }

    public function flush() : void
    {
        if ($this->cacheDir === '') {
            return;
        }

        $path = $this->path();
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * @param list<string> $serviceIds
     * @return array{fingerprint: string, entries: array<string, string>, methods: list<string>}
     */
    private function snapshot(array $serviceIds) : array
    {
        $services = [];

        foreach ($this->collectServiceIds(serviceIds: $serviceIds) as $serviceId) {
            $services[] = $this->services->compile(serviceId: $serviceId);
        }

        usort(
            $services,
            static fn(array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']
        );

        $fingerprint = sha1(serialize([
            'cacheVersion' => $this->cacheVersion,
            'services' => array_map(
                static fn(array $service) => [
                    'serviceId' => $service['serviceId'],
                    'method' => $service['method'],
                    'signature' => $service['signature'],
                ],
                $services
            ),
        ]));

        return [
            'fingerprint' => $fingerprint,
            'entries' => array_column($services, 'method', 'serviceId'),
            'methods' => array_column($services, 'source'),
        ];
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    private function collectServiceIds(array $serviceIds) : array
    {
        $queue = array_values(array_unique(array_merge(
            array_keys($this->registrations->all()),
            $serviceIds
        )));
        $compiled = [];

        while ($queue !== []) {
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift($queue));
            if (isset($compiled[$serviceId])) {
                continue;
            }

            if (! $this->isCompilable(serviceId: $serviceId)) {
                continue;
            }

            $compiled[$serviceId] = true;

            $registration = $this->registrations->get(abstract: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists($serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string($candidate) || ! class_exists($candidate)) {
                continue;
            }

            $blueprint = $this->blueprints->createFor(class: $candidate);
            foreach ($this->dependenciesFor(blueprint: $blueprint) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return array_keys($compiled);
    }

    private function isCompilable(string $serviceId) : bool
    {
        $registration = $this->registrations->get(abstract: $serviceId);
        $candidate = $registration?->concrete;

        if (is_string($candidate) && class_exists($candidate)) {
            return true;
        }

        if ($candidate instanceof Closure || is_object($candidate)) {
            return true;
        }

        return class_exists($serviceId);
    }

    /**
     * @return list<string>
     */
    private function dependenciesFor(ServiceBlueprint $blueprint) : array
    {
        $dependencies = [];

        foreach ($blueprint->constructor?->parameters ?? [] as $parameter) {
            if (is_string($parameter['serviceId'] ?? null)) {
                $dependencies[] = $parameter['serviceId'];
            }
        }

        foreach ($blueprint->injectableProperties ?? [] as $property) {
            if (is_string($property['serviceId'] ?? null)) {
                $dependencies[] = $property['serviceId'];
            }
        }

        foreach ($blueprint->injectableMethods ?? [] as $method) {
            foreach ($method['plan']->parameters as $parameter) {
                if (is_string($parameter['serviceId'] ?? null)) {
                    $dependencies[] = $parameter['serviceId'];
                }
            }
        }

        return array_values(array_unique($dependencies));
    }

    /**
     * @param array{fingerprint: string, entries: array<string, string>, methods: list<string>} $snapshot
     */
    private function sourceFor(array $snapshot) : string
    {
        $entries = var_export($snapshot['entries'], true);
        $methods = implode(PHP_EOL, $snapshot['methods']);

        return <<<PHP
declare(strict_types=1);

namespace Avax\\Container\\Compilation\\Generated;

return new class extends \\Avax\\Container\\Compilation\\CompiledContainer
{
    protected string \$fingerprint = '{$snapshot['fingerprint']}';

    protected array \$entries = {$entries};

{$methods}};
PHP;
    }

    private function loadSource(string $source, string $fingerprint) : CompiledContainer
    {
        /** @var CompiledContainer $compiled */
        $compiled = eval($source);

        return $compiled;
    }

    private function write(string $source) : void
    {
        $path = $this->path();
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException("Cannot create compiled container directory [{$directory}].");
        }

        $temp = $path . '.' . uniqid('tmp', true);
        $body = '<?php' . PHP_EOL . PHP_EOL . $source . PHP_EOL;

        if (file_put_contents($temp, $body, LOCK_EX) === false) {
            throw new RuntimeException("Cannot write compiled container file [{$temp}].");
        }

        if (! rename($temp, $path)) {
            @unlink($temp);
            throw new RuntimeException("Cannot publish compiled container file [{$path}].");
        }

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }
    }

    private function path() : string
    {
        return rtrim($this->cacheDir, '/\\') . '/container/' . rawurlencode($this->cacheVersion) . '/compiled/container.php';
    }
}
