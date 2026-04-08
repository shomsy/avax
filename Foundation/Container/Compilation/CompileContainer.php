<?php

declare(strict_types=1);

namespace Avax\Container\Compilation;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Resolution\LifetimePlan;
use Avax\Container\Errors\ContainerException;
use Avax\Container\Observability\ResolutionMetrics;
use Closure;
use JsonException;
use RuntimeException;
use Throwable;

/**
 * Builds, loads, and invalidates the compiled container artifact set.
 */
final class CompileContainer
{
    private const FORMAT = 'compiled-container';

    private const SCHEMA_VERSION = 4;

    private ServiceCompiler $services;

    private ArtifactMetadata|null $lastMetadata = null;

    public function __construct(
        private readonly ServiceRegistry $registrations,
        private readonly CreateServiceBlueprint $blueprints,
        private readonly string $cacheDir = '',
        private readonly string $cacheVersion = 'container-v1',
        private readonly string $configHash = '',
        private readonly string $diagnosticsMode = 'minimal',
        private readonly string $environment = '',
        private readonly string $compileMode = 'production',
        private readonly bool $strict = false,
        private readonly string $settingsFingerprint = '',
        private readonly string $benchmarkBuildMarker = '',
        private readonly bool $validateOnLoad = false,
        private readonly bool $failClosedOnCorruption = true,
        private readonly bool $validateBeforeCompile = false,
        private readonly ResolutionMetrics|null $metrics = null,
        ServiceCompiler|null $services = null
    ) {
        $this->services = $services ?? new ServiceCompiler(
            registrations: $this->registrations,
            blueprints   : $this->blueprints
        );
    }

    /**
     * Returns whether this compiler must validate before writing artifacts.
     */
    public function shouldValidateBeforeCompile() : bool
    {
        return $this->validateBeforeCompile;
    }

    /**
     * Compiles one container artifact for the requested service set.
     *
     * @param list<string> $serviceIds
     * @param list<string> $validationIssues
     * @throws ContainerException
     */
    public function compile(array $serviceIds = [], array $validationIssues = [], bool $warmed = false) : CompiledContainer
    {
        $snapshot = $this->snapshot(serviceIds: $serviceIds);
        $metadata = $this->metadataFor(
            snapshot        : $snapshot,
            validationIssues: $validationIssues,
            previous        : $this->loadMetadata(quarantineOnFailure: false),
            warmed          : $warmed
        );

        if ($this->cacheDir !== '' && $this->artifactMatches(metadata: $metadata)) {
            $this->metrics?->increment(name: 'container_compiled_container_reuses_total');
            $this->lastMetadata = $metadata;

            try {
                return $this->loadCompiledFromPath(path: $this->path());
            } catch (ContainerException) {
                $this->metrics?->increment(name: 'container_compiled_container_corrupt_total');
                $this->quarantineArtifacts();
            }
        }

        $source = $this->sourceFor(snapshot: $snapshot);
        $compiled = $this->loadSource(source: $source);
        $this->lastMetadata = $metadata;

        if ($this->cacheDir !== '') {
            $this->write(source: $source, metadata: $metadata);
            $this->metrics?->increment(name: 'container_compiled_container_writes_total');
        } else {
            $this->metrics?->increment(name: 'container_compiled_container_builds_total');
        }

        return $compiled;
    }

    /**
     * Loads one compiled container artifact when it is available and fresh.
     *
     * @param list<string> $serviceIds
     */
    public function load(array $serviceIds = []) : CompiledContainer|null
    {
        if ($this->cacheDir === '') {
            return null;
        }

        $metadata = $this->loadMetadata();
        if ($metadata === null) {
            $this->metrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        $this->lastMetadata = $metadata;

        $compatibilityIssues = $this->compatibilityIssuesFor(metadata: $metadata);
        if ($compatibilityIssues !== []) {
            $this->metrics?->increment(name: 'container_compiled_container_incompatible_total');
            $this->metrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        if (! $this->servicesAreAvailable(metadata: $metadata, serviceIds: $serviceIds)) {
            $this->metrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        if (! $this->sourceMatchesChecksum(path: $this->path(), checksum: $metadata->checksum)) {
            $this->handleCorruption(reason: 'Compiled container checksum validation failed.');

            return null;
        }

        if ($this->validateOnLoad && ! $this->requestedServicesAreFresh(metadata: $metadata, serviceIds: $serviceIds)) {
            $this->metrics?->increment(name: 'container_compiled_container_stale_total');

            return null;
        }

        try {
            $compiled = $this->loadCompiledFromPath(path: $this->path());
        } catch (ContainerException) {
            $this->handleCorruption(reason: 'Compiled container artifact could not be loaded.');

            return null;
        }

        if ($compiled->fingerprint() !== $metadata->fingerprint) {
            $this->handleCorruption(reason: 'Compiled container fingerprint does not match its metadata.');

            return null;
        }

        $this->metrics?->increment(name: 'container_compiled_container_hits_total');

        return $compiled;
    }

    /**
     * Removes all compiled container artifacts.
     */
    public function flush() : void
    {
        $this->lastMetadata = null;

        $directory = $this->directory();
        if (! is_dir($directory)) {
            return;
        }

        $this->deleteDirectory(directory: $directory);
    }

    /**
     * Returns whether one service is present in the current compiled metadata.
     */
    public function contains(string $serviceId) : bool
    {
        $report = $this->report(serviceIds: [$serviceId]);

        return $report->available && in_array($serviceId, $report->entries, true);
    }

    /**
     * Returns the current compile status for the requested service set.
     *
     * @param list<string> $serviceIds
     */
    public function report(array $serviceIds = []) : CompileReport
    {
        $metadata = $this->reportMetadata();
        $compatibilityIssues = $metadata !== null
            ? $this->compatibilityIssuesFor(metadata: $metadata)
            : ['compiled metadata is missing'];
        $compatible = $metadata !== null && $compatibilityIssues === [];
        $freshnessState = $this->freshnessStateFor(metadata: $metadata, serviceIds: $serviceIds);
        $checksumValid = $metadata !== null
            && ($this->cacheDir === '' || $this->sourceMatchesChecksum(path: $this->path(), checksum: $metadata->checksum));
        $available = $compatible
            && $freshnessState === 'fresh'
            && ($this->cacheDir === '' || is_file($this->path()))
            && $checksumValid;
        $warnings = $this->warningsFor(
            metadata            : $metadata,
            freshnessState      : $freshnessState,
            compatibilityIssues : $compatibilityIssues,
            checksumValid       : $checksumValid,
            available           : $available
        );

        return new CompileReport(
            available       : $available,
            compatible      : $compatible,
            freshnessState  : $freshnessState,
            warnings        : $warnings,
            path            : $this->path(),
            metadataPath    : $this->metadataPath(),
            cacheVersion    : $this->cacheVersion,
            compileMode     : $this->compileMode,
            environment     : $this->environment,
            fingerprint     : $metadata?->fingerprint ?? '',
            checksumValid   : $checksumValid,
            totalServices   : count($metadata?->services ?? []),
            compiledServicesCount: count($metadata?->entries ?? []),
            reusedServicesCount: (int) ($metadata?->statistics['reusedServices'] ?? 0),
            invalidatedServicesCount: count($metadata?->invalidatedServices ?? []),
            deferredServicesCount: (int) ($metadata?->statistics['deferredServices'] ?? 0),
            lazyServicesCount: (int) ($metadata?->statistics['lazyServices'] ?? 0),
            tagIndexSize    : count($metadata?->tags ?? []),
            aliasMapSize    : count($metadata?->aliases ?? []),
            decorationMapSize: count(array_filter(
                $metadata?->decorations ?? [],
                static fn(int $count) : bool => $count > 0
            )),
            providerBootPlanSize: (int) ($metadata?->statistics['providerBootPlanSize'] ?? 0),
            lifetimePlanSummary: $this->lifetimePlanSummaryFor(metadata: $metadata),
            entries         : $metadata?->entryIds() ?? [],
            changedServices : $metadata?->changedServices ?? [],
            invalidatedServices: $metadata?->invalidatedServices ?? [],
            validationIssues: $metadata?->validationIssues ?? [],
            compatibilityIssues: $compatible ? [] : $compatibilityIssues,
            invalidationReasons: $metadata?->invalidationReasons ?? [],
            statistics      : $metadata?->statistics ?? [],
            metadata        : $metadata
        );
    }

    /**
     * @param list<string> $serviceIds
     * @return array{
     *     fingerprint: string,
     *     entries: array<string, string>,
     *     methods: array<string, string>,
     *     services: array<string, array{method: string, signature: string}>,
     *     sources: array<string, string>,
     *     dependencies: array<string, list<string>>,
     *     aliases: array<string, string>,
     *     tags: array<string, list<string>>,
     *     lifetimes: array<string, array{name: string, shared: bool, scoped: bool, transient: bool}>,
     *     deferred: array<string, bool>,
     *     decorations: array<string, int>
     * }
     */
    private function snapshot(array $serviceIds) : array
    {
        $previous = $this->loadMetadata(quarantineOnFailure: false);
        $services = [];
        $sources = [];
        $dependencies = [];
        $reusedServices = 0;
        $invalidationReasons = [];

        foreach ($this->collectServiceIds(serviceIds: $serviceIds) as $serviceId) {
            $description = $this->services->describe(serviceId: $serviceId);
            $compiled = $this->services->compileFromDescription(description: $description);
            $services[] = $compiled;
            $dependencies[$serviceId] = $this->dependenciesForService(serviceId: $serviceId);

            $canReuse = $previous !== null
                && ($previous->services[$serviceId]['signature'] ?? null) === $compiled['signature']
                && ($previous->dependencies[$serviceId] ?? []) === $dependencies[$serviceId]
                && isset($previous->sources[$serviceId]);

            if ($canReuse) {
                $sources[$serviceId] = $previous->sources[$serviceId];
                $reusedServices++;
                continue;
            }

            $sources[$serviceId] = $compiled['source'];

            if ($previous !== null) {
                $reasons = $this->invalidationReasonsFor(
                    serviceId    : $serviceId,
                    previous     : $previous,
                    current      : $compiled,
                    dependencies : $dependencies[$serviceId]
                );
                $invalidationReasons = array_merge($invalidationReasons, $reasons);
            }
        }

        usort(
            $services,
            static fn(array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']
        );

        $compiledServices = [];
        foreach ($services as $service) {
            $compiledServices[$service['serviceId']] = [
                'method' => $service['method'],
                'signature' => $service['signature'],
            ];
        }

        $aliases = $this->registrations->allAliases();
        ksort($aliases);

        $lifetimePlans = [];
        foreach ($this->registrations->all() as $abstract => $registration) {
            $lifetimePlans[$abstract] = LifetimePlan::fromRegistration(
                serviceId   : $abstract,
                registration: $registration
            )->toArray();
        }
        foreach (array_keys($compiledServices) as $abstract) {
            $lifetimePlans[$abstract] ??= LifetimePlan::fromRegistration(
                serviceId   : $abstract,
                registration: $this->registrations->get(abstract: $abstract)
            )->toArray();
        }
        ksort($lifetimePlans);

        ksort($sources);
        ksort($dependencies);

        $fingerprint = sha1(serialize([
            'cacheVersion' => $this->cacheVersion,
            'configHash' => $this->configHash,
            'environment' => $this->environment,
            'compileMode' => $this->compileMode,
            'strict' => $this->strict,
            'services' => $compiledServices,
            'sources' => $sources,
            'dependencies' => $dependencies,
            'aliases' => $aliases,
            'tags' => $this->registrations->tagIndex(),
            'lifetimes' => $lifetimePlans,
            'deferred' => $this->registrations->deferredMap(),
            'decorations' => $this->registrations->decorationChains(),
            'reusedServices' => $reusedServices,
        ]));

        return [
            'fingerprint' => $fingerprint,
            'entries' => array_column($services, 'method', 'serviceId'),
            'methods' => $sources,
            'services' => $compiledServices,
            'sources' => $sources,
            'dependencies' => $dependencies,
            'aliases' => $aliases,
            'tags' => $this->registrations->tagIndex(),
            'lifetimes' => $lifetimePlans,
            'deferred' => $this->registrations->deferredMap(),
            'decorations' => $this->registrations->decorationChains(),
            'statistics' => [
                'reusedServices' => $reusedServices,
            ],
            'invalidationReasons' => array_values(array_unique($invalidationReasons)),
        ];
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    private function collectServiceIds(array $serviceIds) : array
    {
        $queue = [];

        if ($serviceIds !== []) {
            foreach (array_values(array_unique($serviceIds)) as $serviceId) {
                $queue[] = $serviceId;
            }
        } else {
            foreach ($this->registrations->all() as $registration) {
                if ($registration->deferred) {
                    continue;
                }

                $queue[] = $registration->abstract;
            }
        }

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
     * @return list<string>
     */
    private function dependenciesForService(string $serviceId) : array
    {
        $registration = $this->registrations->get(abstract: $serviceId);
        $candidate = $registration?->concrete;

        if ($candidate === null && class_exists($serviceId)) {
            $candidate = $serviceId;
        }

        if (! is_string($candidate) || ! class_exists($candidate)) {
            return [];
        }

        return $this->dependenciesFor(
            blueprint: $this->blueprints->createFor(class: $candidate)
        );
    }

    /**
     * @param array{
     *     fingerprint: string,
     *     entries: array<string, string>,
     *     methods: array<string, string>
     * } $snapshot
     */
    private function sourceFor(array $snapshot) : string
    {
        $entries = var_export($snapshot['entries'], true);
        $methods = implode(PHP_EOL, array_values($snapshot['methods']));

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

    private function loadSource(string $source) : CompiledContainer
    {
        $directory = $this->cacheDir !== ''
            ? $this->compiledDirectory()
            : sys_get_temp_dir() . '/avax-container-runtime';

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("Cannot create temporary compiled container directory [{$directory}].");
        }

        $path = $directory . '/runtime-' . uniqid('', true) . '.php';
        $body = '<?php' . PHP_EOL . PHP_EOL . $source . PHP_EOL;

        if (file_put_contents($path, $body, LOCK_EX) === false) {
            throw new RuntimeException("Cannot materialize compiled container source [{$path}].");
        }

        try {
            return $this->loadCompiledFromPath(path: $path);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /**
     */
    private function write(string $source, ArtifactMetadata $metadata) : void
    {
        $compiledDirectory = $this->compiledDirectory();
        if (! is_dir($compiledDirectory) && ! mkdir($compiledDirectory, 0775, true) && ! is_dir($compiledDirectory)) {
            throw new RuntimeException("Cannot create compiled container directory [{$compiledDirectory}].");
        }

        $body = '<?php' . PHP_EOL . PHP_EOL . $source . PHP_EOL;
        $json = json_encode($metadata->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;

        $this->writeAtomically(path: $this->path(), body: $body);
        $this->writeAtomically(path: $this->metadataPath(), body: $json);

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->path(), true);
        }
    }

    private function loadCompiledFromPath(string $path) : CompiledContainer
    {
        try {
            $loaded = require $path;
        } catch (Throwable $throwable) {
            throw new ContainerException(
                message : "Compiled container artifact [{$path}] could not be loaded.",
                previous: $throwable
            );
        }

        if (! $loaded instanceof CompiledContainer) {
            throw new ContainerException(message: "Compiled container artifact [{$path}] is invalid.");
        }

        return $loaded;
    }

    private function writeAtomically(string $path, string $body) : void
    {
        $temp = $path . '.' . uniqid('tmp', true);

        if (file_put_contents($temp, $body, LOCK_EX) === false) {
            throw new RuntimeException("Cannot write compiled container artifact [{$temp}].");
        }

        if (! rename($temp, $path)) {
            @unlink($temp);
            throw new RuntimeException("Cannot publish compiled container artifact [{$path}].");
        }
    }

    /**
     * @param ArtifactMetadata|null $previous
     * @param array{
     *     fingerprint: string,
     *     entries: array<string, string>,
     *     services: array<string, array{method: string, signature: string}>,
     *     sources: array<string, string>,
     *     dependencies: array<string, list<string>>,
     *     aliases: array<string, string>,
     *     tags: array<string, list<string>>,
     *     lifetimes: array<string, array{name: string, shared: bool, scoped: bool, transient: bool}>,
     *     deferred: array<string, bool>,
     *     decorations: array<string, int>,
     *     statistics: array<string, int>,
     *     invalidationReasons: list<string>
     * } $snapshot
     * @param list<string> $validationIssues
     */
    private function metadataFor(
        array $snapshot,
        array $validationIssues,
        ArtifactMetadata|null $previous,
        bool $warmed
    ) : ArtifactMetadata
    {
        $previousServices = $previous?->services ?? [];
        $changedServices = [];
        $removedServices = [];

        foreach ($snapshot['services'] as $serviceId => $service) {
            if (($previousServices[$serviceId]['signature'] ?? null) !== $service['signature']) {
                $changedServices[] = $serviceId;
            }
        }

        foreach (array_keys($previousServices) as $serviceId) {
            if (! isset($snapshot['services'][$serviceId])) {
                $removedServices[] = $serviceId;
            }
        }

        $invalidatedServices = array_values(array_unique(array_merge($changedServices, $removedServices)));
        sort($invalidatedServices);
        $dependencyGraphRevision = sha1(serialize($snapshot['dependencies']));

        return new ArtifactMetadata(
            format          : self::FORMAT,
            schemaVersion   : self::SCHEMA_VERSION,
            compiledAt      : gmdate('c'),
            cacheVersion    : $this->cacheVersion,
            configHash      : $this->configHash,
            settingsFingerprint: $this->settingsFingerprint,
            environment     : $this->environment,
            compileMode     : $this->compileMode,
            diagnosticsMode : $this->diagnosticsMode,
            strict          : $this->strict,
            fingerprint     : $snapshot['fingerprint'],
            dependencyGraphRevision: $dependencyGraphRevision,
            artifactPaths   : [
                'compiled' => $this->path(),
                'metadata' => $this->metadataPath(),
                'compiledDirectory' => $this->compiledDirectory(),
                'quarantineDirectory' => $this->quarantineDirectory(),
            ],
            warmed          : $warmed,
            benchmarkBuildMarker: $this->benchmarkBuildMarker,
            entries         : $snapshot['entries'],
            services        : $snapshot['services'],
            sources         : $snapshot['sources'],
            dependencies    : $snapshot['dependencies'],
            aliases         : $snapshot['aliases'],
            tags            : $snapshot['tags'],
            lifetimes       : $snapshot['lifetimes'],
            deferred        : $snapshot['deferred'],
            decorations     : $snapshot['decorations'],
            changedServices : $changedServices,
            invalidatedServices: $invalidatedServices,
            validationIssues: array_values(array_unique($validationIssues)),
            invalidationReasons: array_values(array_unique($snapshot['invalidationReasons'])),
            statistics      : [
                'compiledServices' => count($snapshot['services']),
                'changedServices' => count($changedServices),
                'invalidatedServices' => count($invalidatedServices),
                'aliases' => count($snapshot['aliases']),
                'tags' => count($snapshot['tags']),
                'deferredServices' => count(array_filter($snapshot['deferred'])),
                'lazyServices' => 0,
                'decoratedServices' => count(array_filter(
                    $snapshot['decorations'],
                    static fn(int $count) : bool => $count > 0
                )),
                'providerBootPlanSize' => 0,
                'lifetimePlans' => count($snapshot['lifetimes']),
                'dependencyGraphEdges' => array_sum(array_map(
                    static fn(array $dependencies) : int => count($dependencies),
                    $snapshot['dependencies']
                )),
                'reusedServices' => (int) ($snapshot['statistics']['reusedServices'] ?? 0),
                'validationIssues' => count(array_values(array_unique($validationIssues))),
            ],
            checksum        : sha1('<?php' . PHP_EOL . PHP_EOL . $this->sourceFor(snapshot: $snapshot) . PHP_EOL)
        );
    }

    private function artifactMatches(ArtifactMetadata $metadata) : bool
    {
        $current = $this->loadMetadata(quarantineOnFailure: false);

        return $current instanceof ArtifactMetadata
            && $current->schemaVersion === $metadata->schemaVersion
            && $this->compatibilityIssuesFor(metadata: $current) === []
            && $current->diagnosticsMode === $metadata->diagnosticsMode
            && $current->settingsFingerprint === $metadata->settingsFingerprint
            && $current->dependencyGraphRevision === $metadata->dependencyGraphRevision
            && $current->warmed === $metadata->warmed
            && $current->benchmarkBuildMarker === $metadata->benchmarkBuildMarker
            && $current->fingerprint === $metadata->fingerprint
            && $this->sourceMatchesChecksum(path: $this->path(), checksum: $current->checksum);
    }

    private function servicesAreAvailable(ArtifactMetadata $metadata, array $serviceIds) : bool
    {
        return $metadata->includes(serviceIds: $serviceIds);
    }

    private function requestedServicesAreFresh(ArtifactMetadata $metadata, array $serviceIds) : bool
    {
        $ids = $this->requestedServiceClosure(metadata: $metadata, serviceIds: $serviceIds);

        foreach ($ids as $serviceId) {
            $current = $this->services->describe(serviceId: $serviceId);
            if (($metadata->services[$serviceId]['signature'] ?? null) !== $current['signature']) {
                return false;
            }

            if (($metadata->dependencies[$serviceId] ?? []) !== $this->dependenciesForService(serviceId: $serviceId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    private function requestedServiceClosure(ArtifactMetadata $metadata, array $serviceIds) : array
    {
        $queue = $serviceIds !== []
            ? array_values(array_unique($serviceIds))
            : array_keys($metadata->services);
        $seen = [];

        while ($queue !== []) {
            $serviceId = (string) array_shift($queue);
            if (isset($seen[$serviceId])) {
                continue;
            }

            $seen[$serviceId] = true;
            foreach ($metadata->dependencies[$serviceId] ?? [] as $dependency) {
                if (! isset($seen[$dependency])) {
                    $queue[] = $dependency;
                }
            }
        }

        $ids = array_keys($seen);
        sort($ids);

        return $ids;
    }

    private function sourceMatchesChecksum(string $path, string $checksum) : bool
    {
        if ($checksum === '' || ! is_file($path)) {
            return false;
        }

        $body = file_get_contents($path);

        return is_string($body) && sha1($body) === $checksum;
    }

    /**
     */
    private function loadMetadata(bool $quarantineOnFailure = true) : ArtifactMetadata|null
    {
        if ($this->cacheDir === '') {
            return null;
        }

        $path = $this->metadataPath();
        if (! is_file($path)) {
            return null;
        }

        $json = file_get_contents($path);
        if (! is_string($json) || $json === '') {
            if ($quarantineOnFailure) {
                $this->handleCorruption(reason: 'Compiled container metadata could not be read.');
            }

            return null;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            if ($quarantineOnFailure) {
                $this->handleCorruption(reason: 'Compiled container metadata JSON is invalid.');
            }

            return null;
        }

        if (! is_array($decoded) || ($decoded['format'] ?? '') !== self::FORMAT) {
            if ($quarantineOnFailure) {
                $this->handleCorruption(reason: 'Compiled container metadata format is invalid.');
            }

            return null;
        }

        return ArtifactMetadata::fromArray(state: $decoded);
    }

    private function reportMetadata() : ArtifactMetadata|null
    {
        return $this->loadMetadata(quarantineOnFailure: false) ?? $this->lastMetadata;
    }

    /**
     * @param array{method: string, signature: string, source: string} $current
     * @param list<string> $dependencies
     * @return list<string>
     */
    private function invalidationReasonsFor(
        string $serviceId,
        ArtifactMetadata $previous,
        array $current,
        array $dependencies
    ) : array {
        $reasons = [];

        if (($previous->services[$serviceId]['signature'] ?? null) !== $current['signature']) {
            $reasons[] = "signature changed for [{$serviceId}]";
        }

        if (($previous->dependencies[$serviceId] ?? []) !== $dependencies) {
            $reasons[] = "dependency graph changed for [{$serviceId}]";
        }

        if (! isset($previous->sources[$serviceId])) {
            $reasons[] = "no previous compiled source for [{$serviceId}]";
        }

        return $reasons;
    }

    /**
     * @return list<string>
     */
    private function compatibilityIssuesFor(ArtifactMetadata $metadata) : array
    {
        $issues = [];

        if ($metadata->cacheVersion !== $this->cacheVersion) {
            $issues[] = 'cache version mismatch';
        }
        if ($metadata->schemaVersion !== self::SCHEMA_VERSION) {
            $issues[] = 'schema version mismatch';
        }
        if ($metadata->configHash !== $this->configHash) {
            $issues[] = 'config hash mismatch';
        }
        if ($metadata->environment !== $this->environment) {
            $issues[] = 'environment mismatch';
        }
        if ($metadata->compileMode !== $this->compileMode) {
            $issues[] = 'compile mode mismatch';
        }
        if ($metadata->diagnosticsMode !== $this->diagnosticsMode) {
            $issues[] = 'diagnostics mode mismatch';
        }
        if ($metadata->strict !== $this->strict) {
            $issues[] = 'strict mode mismatch';
        }

        return $issues;
    }

    private function handleCorruption(string $reason) : void
    {
        $this->metrics?->increment(name: 'container_compiled_container_corrupt_total');
        $this->quarantineArtifacts();

        if ($this->failClosedOnCorruption) {
            throw new ContainerException(message: $reason);
        }
    }

    private function quarantineArtifacts() : void
    {
        $compiledPath = $this->path();
        $metadataPath = $this->metadataPath();
        $quarantineDirectory = $this->quarantineDirectory();
        if (! is_dir($quarantineDirectory) && ! mkdir($quarantineDirectory, 0775, true) && ! is_dir($quarantineDirectory)) {
            return;
        }

        $suffix = 'container.' . gmdate('YmdHis') . '.' . substr(sha1(uniqid('', true)), 0, 8);

        if (is_file($compiledPath)) {
            @rename($compiledPath, $quarantineDirectory . '/' . $suffix . '.php');
        }

        if (is_file($metadataPath)) {
            @rename($metadataPath, $quarantineDirectory . '/' . $suffix . '.json');
        }

        $this->metrics?->increment(name: 'container_compiled_container_quarantines_total');
    }

    private function directory() : string
    {
        return rtrim($this->cacheDir, '/\\') . '/container/' . rawurlencode($this->cacheVersion);
    }

    private function compiledDirectory() : string
    {
        return $this->directory() . '/compiled';
    }

    private function path() : string
    {
        return $this->compiledDirectory() . '/container.php';
    }

    private function metadataPath() : string
    {
        return $this->compiledDirectory() . '/container.json';
    }

    private function quarantineDirectory() : string
    {
        return $this->compiledDirectory() . '/quarantine';
    }

    /**
     * @param list<string> $serviceIds
     */
    private function freshnessStateFor(ArtifactMetadata|null $metadata, array $serviceIds) : string
    {
        if (! $metadata instanceof ArtifactMetadata) {
            return 'missing';
        }

        if ($this->compatibilityIssuesFor(metadata: $metadata) !== []) {
            return 'incompatible';
        }

        if (! $this->servicesAreAvailable(metadata: $metadata, serviceIds: $serviceIds)) {
            return 'partial';
        }

        if ($this->cacheDir === '') {
            return 'fresh';
        }

        if (! $this->sourceMatchesChecksum(path: $this->path(), checksum: $metadata->checksum)) {
            return 'corrupt';
        }

        if ($this->validateOnLoad && ! $this->requestedServicesAreFresh(metadata: $metadata, serviceIds: $serviceIds)) {
            return 'stale';
        }

        return 'fresh';
    }

    /**
     * @return array<string, int>
     */
    private function lifetimePlanSummaryFor(ArtifactMetadata|null $metadata) : array
    {
        $summary = [];

        foreach ($metadata?->lifetimes ?? [] as $plan) {
            $name = (string) ($plan['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $summary[$name] = ($summary[$name] ?? 0) + 1;
        }

        ksort($summary);

        return $summary;
    }

    /**
     * @param list<string> $compatibilityIssues
     * @return list<string>
     */
    private function warningsFor(
        ArtifactMetadata|null $metadata,
        string $freshnessState,
        array $compatibilityIssues,
        bool $checksumValid,
        bool $available
    ) : array {
        $warnings = [];

        if (! $available) {
            $warnings[] = match ($freshnessState) {
                'missing' => 'compiled artifact is missing',
                'incompatible' => 'compiled artifact is incompatible with the current runtime',
                'partial' => 'compiled artifact does not contain every requested service',
                'corrupt' => 'compiled artifact checksum is invalid',
                'stale' => 'compiled artifact signatures are stale',
                default => 'compiled artifact is unavailable',
            };
        }

        foreach ($compatibilityIssues as $issue) {
            $warnings[] = $issue;
        }

        if (! $checksumValid && $metadata instanceof ArtifactMetadata) {
            $warnings[] = 'compiled artifact checksum is invalid';
        }

        foreach ($metadata?->validationIssues ?? [] as $issue) {
            $warnings[] = $issue;
        }

        $warnings = array_values(array_unique($warnings));
        sort($warnings);

        return $warnings;
    }

    private function deleteDirectory(string $directory) : void
    {
        $files = scandir($directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory(directory: $path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
