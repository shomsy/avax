<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Compilation;

use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\DependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Resolution\LifetimePlan;
use Closure;
use JsonException;
use ReflectionException;
use RuntimeException;
use SensitiveParameter;
use Throwable;

/**
 * Builds, loads, and invalidates the compiled container artifact set.
 */
final class CompileContainer
{
    private const string FORMAT = 'compiled-container';

    private const int SCHEMA_VERSION = 8;

    private readonly DependencyCompiler $dependencyCompiler;

    private ?ArtifactMetadata $artifactMetadata = null;

    private readonly bool $validateBeforeCompile;

    private readonly bool $failClosedOnCorruption;

    private readonly bool $validateOnLoad;

    private readonly string $pruneMode;

    private readonly string $executionMode;

    private readonly string $benchmarkBuildMarker;

    private readonly string $settingsFingerprint;

    private readonly bool $strict;

    private readonly string $compileMode;

    private readonly string $environment;

    private readonly string $diagnosticsMode;

    private readonly string $configHash;

    private readonly string $cacheVersion;

    private readonly string $cacheDir;

    public function __construct(
        private readonly DependencyRegistry $dependencyRegistry,
        private readonly CreateDependencyBlueprint $createDependencyBlueprint,
        ?string $cacheDir = null,
        ?string $cacheVersion = null,
        #[SensitiveParameter]
        ?string $configHash = null,
        ?string $diagnosticsMode = null,
        ?string $environment = null,
        ?string $compileMode = null,
        ?bool $strict = null,
        ?string $settingsFingerprint = null,
        ?string $benchmarkBuildMarker = null,
        ?string $executionMode = null,
        ?string $pruneMode = null,
        ?bool $validateOnLoad = null,
        ?bool $failClosedOnCorruption = null,
        ?bool $validateBeforeCompile = null,
        private readonly ?ResolutionMetrics $resolutionMetrics = null,
        ?DependencyCompiler $dependencyCompiler = null,
    ) {
        $cacheDir ??= '';
        $cacheVersion ??= 'container-v1';
        $configHash ??= '';
        $diagnosticsMode ??= 'minimal';
        $environment ??= '';
        $compileMode ??= 'production';
        $strict ??= false;
        $settingsFingerprint ??= '';
        $benchmarkBuildMarker ??= '';
        $executionMode ??= CreateContainerConfig::EXECUTION_MODE_COMPILED;
        $pruneMode ??= CreateContainerConfig::PRUNE_MODE_NONE;
        $validateOnLoad ??= false;
        $failClosedOnCorruption ??= true;
        $validateBeforeCompile ??= false;
        $this->cacheDir = $cacheDir;
        $this->cacheVersion = $cacheVersion;
        $this->configHash = $configHash;
        $this->diagnosticsMode = $diagnosticsMode;
        $this->environment = $environment;
        $this->compileMode = $compileMode;
        $this->strict = $strict;
        $this->settingsFingerprint = $settingsFingerprint;
        $this->benchmarkBuildMarker = $benchmarkBuildMarker;
        $this->executionMode = $executionMode;
        $this->pruneMode = $pruneMode;
        $this->validateOnLoad = $validateOnLoad;
        $this->failClosedOnCorruption = $failClosedOnCorruption;
        $this->validateBeforeCompile = $validateBeforeCompile;
        $this->dependencyCompiler = $dependencyCompiler ?? new DependencyCompiler(
            registrations: $this->dependencyRegistry,
            blueprints   : $this->createDependencyBlueprint,
        );
    }

    /**
     * Returns whether this compiler must validate before writing artifacts.
     */
    public function shouldValidateBeforeCompile(): bool
    {
        return $this->validateBeforeCompile;
    }

    /**
     * Compiles one container artifact for the requested service set.
     *
     * @param  list<string>  $serviceIds
     * @param  list<string>  $validationIssues
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    public function compile(?array $serviceIds = null, ?array $validationIssues = null, bool $warmed = false): CompiledContainer
    {
        $serviceIds ??= [];
        $validationIssues ??= [];
        $snapshot = $this->snapshot(serviceIds: $serviceIds);
        $artifactMetadata = $this->metadataFor(
            snapshot        : $snapshot,
            validationIssues: $validationIssues,
            warmed          : $warmed,
            previous        : $this->loadMetadata(quarantineOnFailure: false),
        );

        if ($this->cacheDir !== '' && $this->artifactMatches(metadata: $artifactMetadata)) {
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_reuses_total');
            $this->artifactMetadata = $artifactMetadata;

            try {
                return $this->loadCompiledFromPath(path: $this->path());
            } catch (ContainerException) {
                $this->resolutionMetrics?->increment(name: 'container_compiled_container_corrupt_total');
                $this->quarantineArtifacts();
            }
        }

        $source = $this->sourceFor(snapshot: $snapshot);
        $compiledContainer = $this->loadSource(source: $source);
        $this->artifactMetadata = $artifactMetadata;

        if ($this->cacheDir !== '') {
            $this->write(source: $source, metadata: $artifactMetadata);
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_writes_total');
        } else {
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_builds_total');
        }

        return $compiledContainer;
    }

    /**
     * @param  list<string>  $serviceIds
     * @return array{
     *     fingerprint: string,
     *     entries: array<string, string>,
     *     methods: array<string, string>,
     *     services: array<string, array{method: string, signature: string}>,
     *     sources: array<string, string>,
     *     dependencies: array<string, list<string>>,
     *     aliases: array<string, string>,
     *     tags: array<string, list<string>>,
     *     lifetimes: array<string, array{name: string, shared: bool, scoped: bool, transient: bool, pooled: bool,
     *     poolSize: int, poolResetBeforeReuse: bool}>, deferred: array<string, bool>, decorations: array<string, int>
     * }
     *
     * @throws ReflectionException
     */
    private function snapshot(array $serviceIds): array
    {
        $previous = $this->loadMetadata(quarantineOnFailure: false);
        $selectedServiceIds = $this->collectServiceIds(serviceIds: $serviceIds);
        $services = [];
        $sources = [];
        $dependencies = [];
        $reusedServices = 0;
        $invalidationReasons = [];

        foreach ($selectedServiceIds as $selectedServiceId) {
            $description = $this->dependencyCompiler->describe(serviceId: $selectedServiceId);
            $compiled = $this->dependencyCompiler->compileFromDescription(description: $description);
            $services[] = $compiled;
            $dependencies[$selectedServiceId] = $this->dependenciesForService(serviceId: $selectedServiceId);

            $canReuse = $previous instanceof ArtifactMetadata
                && ($previous->services[$selectedServiceId]['signature'] ?? null) === $compiled['signature']
                && ($previous->dependencies[$selectedServiceId] ?? []) === $dependencies[$selectedServiceId]
                && isset($previous->sources[$selectedServiceId]);

            if ($canReuse) {
                $sources[$selectedServiceId] = $previous->sources[$selectedServiceId];
                $reusedServices++;

                continue;
            }

            $sources[$selectedServiceId] = $compiled['source'];

            if ($previous instanceof ArtifactMetadata) {
                $reasons = $this->invalidationReasonsFor(
                    serviceId   : $selectedServiceId,
                    current     : $compiled,
                    dependencies: $dependencies[$selectedServiceId],
                    previous    : $previous,
                );
                $invalidationReasons = array_merge($invalidationReasons, $reasons);
            }
        }

        usort(
            array   : $services,
            callback: static fn (array $left, array $right): int => $left['serviceId'] <=> $right['serviceId'],
        );

        $compiledServices = [];
        foreach ($services as $service) {
            $compiledServices[$service['serviceId']] = [
                'method' => $service['method'],
                'signature' => $service['signature'],
            ];
        }

        $aliases = $this->dependencyRegistry->allAliases();
        ksort(array: $aliases);

        $lifetimePlans = [];
        foreach ($this->dependencyRegistry->all() as $abstract => $dependencyRegistration) {
            $lifetimePlans[$abstract] = LifetimePlan::fromRegistration(
                serviceId   : $abstract,
                registration: $dependencyRegistration,
            )->toArray();
        }

        foreach (array_keys(array: $compiledServices) as $abstract) {
            $lifetimePlans[$abstract] ??= LifetimePlan::fromRegistration(
                serviceId   : $abstract,
                registration: $this->dependencyRegistry->get(abstract: $abstract),
            )->toArray();
        }

        ksort(array: $lifetimePlans);

        ksort(array: $sources);
        ksort(array: $dependencies);

        $fingerprint = sha1(string: serialize(value: [
            'cacheVersion' => $this->cacheVersion,
            'configHash' => $this->configHash,
            'environment' => $this->environment,
            'compileMode' => $this->compileMode,
            'executionMode' => $this->executionMode,
            'pruneMode' => $this->pruneMode,
            'strict' => $this->strict,
            'services' => $compiledServices,
            'sources' => $sources,
            'dependencies' => $dependencies,
            'aliases' => $aliases,
            'tags' => $this->dependencyRegistry->tagIndex(),
            'lifetimes' => $lifetimePlans,
            'deferred' => $this->dependencyRegistry->deferredMap(),
            'decorations' => $this->dependencyRegistry->decorationChains(),
            'ownership' => $this->dependencyRegistry->ownershipMap(),
            'slices' => $this->dependencyRegistry->sliceManifests(),
            'pruning' => [
                'mode' => $this->pruneMode,
                'rootServices' => $this->pruneRoots(serviceIds: $serviceIds),
                'prunedServices' => $this->dependencyRegistry->all()
                        |> array_keys(...)
                        |> (static fn ($x): array => array_diff($x, $selectedServiceIds))
                        |> array_values(...),
            ],
            'reusedServices' => $reusedServices,
        ]));

        return [
            'fingerprint' => $fingerprint,
            'entries' => array_column(array: $services, column_key: 'method', index_key: 'serviceId'),
            'methods' => $sources,
            'services' => $compiledServices,
            'sources' => $sources,
            'dependencies' => $dependencies,
            'aliases' => $aliases,
            'tags' => $this->dependencyRegistry->tagIndex(),
            'lifetimes' => $lifetimePlans,
            'deferred' => $this->dependencyRegistry->deferredMap(),
            'decorations' => $this->dependencyRegistry->decorationChains(),
            'ownership' => $this->dependencyRegistry->ownershipMap(),
            'slices' => $this->dependencyRegistry->sliceManifests(),
            'pruning' => [
                'mode' => $this->pruneMode,
                'rootServices' => $this->pruneRoots(serviceIds: $serviceIds),
                'prunedServices' => $this->dependencyRegistry->all()
                        |> array_keys(...)
                        |> (static fn ($x): array => array_diff($x, $selectedServiceIds))
                        |> array_values(...),
                'reasons' => $this->pruneMode === CreateContainerConfig::PRUNE_MODE_STRICT
                    ? [
                        'strict pruning keeps only safe root services and their proven transitive closure',
                        'deferred, conditional, grouped, tagged, aliased, contextual, and fallback services remain roots to avoid unsafe pruning',
                    ]
                    : [],
            ],
            'statistics' => [
                'reusedServices' => $reusedServices,
            ],
            'invalidationReasons' => array_values(array: array_unique(array: $invalidationReasons)),
        ];
    }

    private function loadMetadata(bool $quarantineOnFailure = true): ?ArtifactMetadata
    {
        if ($this->cacheDir === '') {
            return null;
        }

        $path = $this->metadataPath();
        if (! is_file(filename: $path)) {
            return null;
        }

        $json = file_get_contents(filename: $path);
        if (! is_string(value: $json) || $json === '') {
            if ($quarantineOnFailure) {
                $this->handleCorruption(reason: 'Compiled container metadata could not be read.');
            }

            return null;
        }

        try {
            $decoded = json_decode(json: $json, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            if ($quarantineOnFailure) {
                $this->handleCorruption(reason: 'Compiled container metadata JSON is invalid.');
            }

            return null;
        }

        if (! is_array(value: $decoded) || ($decoded['format'] ?? '') !== self::FORMAT) {
            if ($quarantineOnFailure) {
                $this->handleCorruption(reason: 'Compiled container metadata format is invalid.');
            }

            return null;
        }

        return ArtifactMetadata::fromArray(state: $decoded);
    }

    private function metadataPath(): string
    {
        return $this->compiledDirectory().'/container.json';
    }

    private function compiledDirectory(): string
    {
        return $this->directory().'/compiled';
    }

    private function directory(): string
    {
        return rtrim(string: $this->cacheDir, characters: '/\\').'/container/'.rawurlencode(string: $this->cacheVersion);
    }

    private function handleCorruption(string $reason): void
    {
        $this->resolutionMetrics?->increment(name: 'container_compiled_container_corrupt_total');
        $this->quarantineArtifacts();

        if ($this->failClosedOnCorruption) {
            throw new ContainerException(message: $reason);
        }
    }

    private function quarantineArtifacts(): void
    {
        $compiledPath = $this->path();
        $metadataPath = $this->metadataPath();
        $quarantineDirectory = $this->quarantineDirectory();
        if (! is_dir(filename: $quarantineDirectory) && ! mkdir(directory: $quarantineDirectory, permissions: 0o775, recursive: true) && ! is_dir(filename: $quarantineDirectory)) {
            return;
        }

        $suffix = 'container.'.gmdate(format: 'YmdHis').'.'.uniqid(prefix: '', more_entropy: true)
                |> sha1(...)
                |> (static fn ($x): string => substr(string: (string) $x, offset: 0, length: 8));

        if (is_file(filename: $compiledPath)) {
            rename(from: $compiledPath, to: $quarantineDirectory.'/'.$suffix.'.php');
        }

        if (is_file(filename: $metadataPath)) {
            rename(from: $metadataPath, to: $quarantineDirectory.'/'.$suffix.'.json');
        }

        $this->resolutionMetrics?->increment(name: 'container_compiled_container_quarantines_total');
    }

    private function path(): string
    {
        return $this->compiledDirectory().'/container.php';
    }

    private function quarantineDirectory(): string
    {
        return $this->compiledDirectory().'/quarantine';
    }

    /**
     * @param  list<string>  $serviceIds
     * @return list<string>
     *
     * @throws ReflectionException
     */
    private function collectServiceIds(array $serviceIds): array
    {
        $queue = $this->pruneRoots(serviceIds: $serviceIds);

        $compiled = [];

        while ($queue !== []) {
            $serviceId = $this->dependencyRegistry->resolveAlias(abstract: array_shift(array: $queue));
            if (isset($compiled[$serviceId])) {
                continue;
            }

            if (! $this->isCompilable(serviceId: $serviceId)) {
                continue;
            }

            $compiled[$serviceId] = true;

            $registration = $this->dependencyRegistry->get(abstract: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists(class: $serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string(value: $candidate)) {
                continue;
            }

            if (! class_exists(class: $candidate)) {
                continue;
            }

            $blueprint = $this->createDependencyBlueprint->createFor(class: $candidate);
            foreach ($this->dependenciesFor(blueprint: $blueprint) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return array_keys(array: $compiled);
    }

    /**
     * @param  list<string>  $serviceIds
     * @return list<string>
     */
    private function pruneRoots(array $serviceIds): array
    {
        if ($serviceIds !== []) {
            $roots = array_map(
                callback: fn (string $serviceId): string => $this->dependencyRegistry->resolveAlias(abstract: $serviceId),
                array   : $serviceIds,
            )
                    |> array_unique(...)
                    |> array_values(...);
            sort(array: $roots);

            return $roots;
        }

        if ($this->pruneMode !== CreateContainerConfig::PRUNE_MODE_STRICT) {
            $roots = [];

            foreach ($this->dependencyRegistry->all() as $dependencyRegistration) {
                if ($dependencyRegistration->deferred) {
                    continue;
                }

                $roots[] = $dependencyRegistration->abstract;
            }

            sort(array: $roots);

            return $roots;
        }

        $roots = [];

        foreach ($this->dependencyRegistry->all() as $serviceId => $dependencyRegistration) {
            if (($this->dependencyRegistry->topLevelAccessTo(serviceId: $serviceId)['allowed'] ?? false) === true) {
                $roots[] = $serviceId;
            }

            if (
                $dependencyRegistration->deferred
                || $dependencyRegistration->metadata->hasConditions()
                || $dependencyRegistration->metadata->fallback
                || $dependencyRegistration->group !== null
                || $dependencyRegistration->tags !== []
            ) {
                $roots[] = $serviceId;
            }

            foreach ($this->dependencyRegistry->decorationChain(abstract: $serviceId) as $descriptor) {
                if (is_string(value: $descriptor) && $this->dependencyRegistry->has(abstract: $descriptor)) {
                    $roots[] = $descriptor;
                }
            }
        }

        foreach ($this->dependencyRegistry->allAliases() as $target) {
            $roots[] = $target;
        }

        foreach ($this->dependencyRegistry->contextual() as $consumer => $rules) {
            $roots[] = $consumer;

            foreach ($rules as $rule) {
                if (is_string(value: $rule)) {
                    $roots[] = $this->dependencyRegistry->resolveAlias(abstract: $rule);
                }
            }
        }

        $roots = array_filter(
            array   : $roots,
            callback: static fn (string $serviceId): bool => $serviceId !== '',
        )
                |> array_unique(...)
                |> array_values(...);
        sort(array: $roots);

        return $roots;
    }

    private function isCompilable(string $serviceId): bool
    {
        $registration = $this->dependencyRegistry->get(abstract: $serviceId);
        $candidate = $registration?->concrete;

        if (is_string(value: $candidate) && class_exists(class: $candidate)) {
            return true;
        }

        if ($candidate instanceof Closure || is_object(value: $candidate)) {
            return true;
        }

        return class_exists(class: $serviceId);
    }

    /**
     * @return list<string>
     */
    private function dependenciesFor(DependencyBlueprint $dependencyBlueprint): array
    {
        $dependencies = [];

        foreach ($dependencyBlueprint->constructor?->parameters ?? [] as $parameter) {
            if (is_string(value: $parameter['serviceId'] ?? null)) {
                $dependencies[] = $parameter['serviceId'];
            }
        }

        foreach ($dependencyBlueprint->injectableProperties ?? [] as $property) {
            if (is_string(value: $property['serviceId'] ?? null)) {
                $dependencies[] = $property['serviceId'];
            }
        }

        foreach ($dependencyBlueprint->injectableMethods ?? [] as $method) {
            foreach ($method['plan']->parameters as $parameter) {
                if (is_string(value: $parameter['serviceId'] ?? null)) {
                    $dependencies[] = $parameter['serviceId'];
                }
            }
        }

        return array_values(array: array_unique(array: $dependencies));
    }

    /**
     * @return list<string>
     *
     * @throws ReflectionException
     */
    private function dependenciesForService(string $serviceId): array
    {
        $registration = $this->dependencyRegistry->get(abstract: $serviceId);
        $candidate = $registration?->concrete;

        if ($candidate === null && class_exists(class: $serviceId)) {
            $candidate = $serviceId;
        }

        if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
            return [];
        }

        return $this->dependenciesFor(
            blueprint: $this->createDependencyBlueprint->createFor(class: $candidate),
        );
    }

    /**
     * @param  array{method: string, signature: string, source: string}  $current
     * @param  list<string>  $dependencies
     * @return list<string>
     */
    private function invalidationReasonsFor(
        string $serviceId,
        ArtifactMetadata $artifactMetadata,
        array $current,
        array $dependencies,
    ): array {
        $reasons = [];

        if (($artifactMetadata->services[$serviceId]['signature'] ?? null) !== $current['signature']) {
            $reasons[] = sprintf('signature changed for [%s]', $serviceId);
        }

        if (($artifactMetadata->dependencies[$serviceId] ?? []) !== $dependencies) {
            $reasons[] = sprintf('dependency graph changed for [%s]', $serviceId);
        }

        if (! isset($artifactMetadata->sources[$serviceId])) {
            $reasons[] = sprintf('no previous compiled source for [%s]', $serviceId);
        }

        return $reasons;
    }

    /**
     * @param array{
     *     fingerprint: string,
     *     entries: array<string, string>,
     *     services: array<string, array{method: string, signature: string}>,
     *     sources: array<string, string>,
     *     dependencies: array<string, list<string>>,
     *     aliases: array<string, string>,
     *     tags: array<string, list<string>>,
     *     lifetimes: array<string, array{name: string, shared: bool, scoped: bool, transient: bool, pooled: bool,
     *     poolSize: int, poolResetBeforeReuse: bool}>, deferred: array<string, bool>, decorations: array<string, int>,
     *     ownership: array<string, array<string, mixed>>, slices: array<string, array<string, mixed>>, pruning:
     *     array<string, mixed>, statistics: array<string, int>, invalidationReasons: list<string> } $snapshot
     * @param  list<string>  $validationIssues
     */
    private function metadataFor(
        array $snapshot,
        array $validationIssues,
        ?ArtifactMetadata $artifactMetadata,
        bool $warmed,
    ): ArtifactMetadata {
        $previousServices = $artifactMetadata?->services ?? [];
        $changedServices = [];
        $removedServices = [];

        foreach ($snapshot['services'] as $serviceId => $service) {
            if (($previousServices[$serviceId]['signature'] ?? null) !== $service['signature']) {
                $changedServices[] = $serviceId;
            }
        }

        foreach (array_keys(array: $previousServices) as $serviceId) {
            if (! isset($snapshot['services'][$serviceId])) {
                $removedServices[] = $serviceId;
            }
        }

        $invalidatedServices = array_merge($changedServices, $removedServices)
                |> array_unique(...)
                |> array_values(...);
        sort(array: $invalidatedServices);
        $dependencyGraphRevision = sha1(string: serialize(value: $snapshot['dependencies']));

        return new ArtifactMetadata(
            format                 : self::FORMAT,
            schemaVersion          : self::SCHEMA_VERSION,
            compiledAt             : gmdate(format: 'c'),
            cacheVersion           : $this->cacheVersion,
            configHash             : $this->configHash,
            settingsFingerprint    : $this->settingsFingerprint,
            environment            : $this->environment,
            compileMode            : $this->compileMode,
            executionMode          : $this->executionMode,
            pruneMode              : $this->pruneMode,
            diagnosticsMode        : $this->diagnosticsMode,
            strict                 : $this->strict,
            fingerprint            : $snapshot['fingerprint'],
            dependencyGraphRevision: $dependencyGraphRevision,
            artifactPaths          : [
                'compiled' => $this->path(),
                'metadata' => $this->metadataPath(),
                'compiledDirectory' => $this->compiledDirectory(),
                'quarantineDirectory' => $this->quarantineDirectory(),
            ],
            warmed                 : $warmed,
            benchmarkBuildMarker   : $this->benchmarkBuildMarker,
            entries                : $snapshot['entries'],
            services               : $snapshot['services'],
            sources                : $snapshot['sources'],
            dependencies           : $snapshot['dependencies'],
            aliases                : $snapshot['aliases'],
            tags                   : $snapshot['tags'],
            lifetimes              : $snapshot['lifetimes'],
            deferred               : $snapshot['deferred'],
            decorations            : $snapshot['decorations'],
            ownership              : $snapshot['ownership'],
            slices                 : $snapshot['slices'],
            pruning                : $snapshot['pruning'],
            changedServices        : $changedServices,
            invalidatedServices    : $invalidatedServices,
            validationIssues       : array_values(array: array_unique(array: $validationIssues)),
            invalidationReasons    : array_values(array: array_unique(array: $snapshot['invalidationReasons'])),
            statistics             : [
                'compiledServices' => count(value: $snapshot['services']),
                'changedServices' => count(value: $changedServices),
                'invalidatedServices' => count(value: $invalidatedServices),
                'aliases' => count(value: $snapshot['aliases']),
                'tags' => count(value: $snapshot['tags']),
                'deferredServices' => count(value: array_filter(array: $snapshot['deferred'])),
                'lazyServices' => 0,
                'decoratedServices' => count(value: array_filter(
                    array   : $snapshot['decorations'],
                    callback: static fn (int $count): bool => $count > 0,
                )),
                'ownershipUnits' => count(value: $snapshot['ownership']),
                'sliceCount' => count(value: $snapshot['slices']),
                'providerBootPlanSize' => 0,
                'lifetimePlans' => count(value: $snapshot['lifetimes']),
                'pooledServices' => count(value: array_filter(
                    array   : $snapshot['lifetimes'],
                    callback: static fn (array $plan): bool => (bool) ($plan['pooled'] ?? false),
                )),
                'dependencyGraphEdges' => array_sum(array: array_map(
                    callback: static fn (array $dependencies): int => count(value: $dependencies),
                    array   : $snapshot['dependencies'],
                )),
                'reusedServices' => (int) ($snapshot['statistics']['reusedServices'] ?? 0),
                'validationIssues' => $validationIssues
                        |> array_unique(...)
                        |> array_values(...)
                        |> count(...),
            ],
            checksum               : sha1(string: '<?php'.PHP_EOL.PHP_EOL.$this->sourceFor(snapshot: $snapshot).PHP_EOL),
        );
    }

    /**
     * @param array{
     *     fingerprint: string,
     *     entries: array<string, string>,
     *     methods: array<string, string>
     * } $snapshot
     */
    private function sourceFor(array $snapshot): string
    {
        $entries = var_export(value: $snapshot['entries'], return: true);
        $methods = implode(separator: PHP_EOL, array: array_values(array: $snapshot['methods']));

        return <<<PHP
            declare(strict_types=1);
            
            namespace Avax\\Components\\Application\\Container\\DI\\Capabilities\\Composition\\Compilation\\Generated;
            
            return new class extends \\Avax\\Container\\Capabilities\\Composition\\Compilation\\CompiledContainer
            {
                protected string \$fingerprint = '{$snapshot['fingerprint']}';
            
                protected array \$entries = {$entries};
            
            {$methods}};
            PHP;
    }

    private function artifactMatches(ArtifactMetadata $artifactMetadata): bool
    {
        $current = $this->loadMetadata(quarantineOnFailure: false);

        return $current instanceof ArtifactMetadata
            && $current->schemaVersion === $artifactMetadata->schemaVersion
            && $this->compatibilityIssuesFor(metadata: $current) === []
            && $current->diagnosticsMode === $artifactMetadata->diagnosticsMode
            && $current->executionMode === $artifactMetadata->executionMode
            && $current->pruneMode === $artifactMetadata->pruneMode
            && $current->settingsFingerprint === $artifactMetadata->settingsFingerprint
            && $current->dependencyGraphRevision === $artifactMetadata->dependencyGraphRevision
            && $current->warmed === $artifactMetadata->warmed
            && $current->benchmarkBuildMarker === $artifactMetadata->benchmarkBuildMarker
            && $current->fingerprint === $artifactMetadata->fingerprint
            && $this->sourceMatchesChecksum(path: $this->path(), checksum: $current->checksum);
    }

    /**
     * @return list<string>
     */
    private function compatibilityIssuesFor(ArtifactMetadata $artifactMetadata): array
    {
        $issues = [];

        if ($artifactMetadata->cacheVersion !== $this->cacheVersion) {
            $issues[] = 'cache version mismatch';
        }

        if ($artifactMetadata->schemaVersion !== self::SCHEMA_VERSION) {
            $issues[] = 'schema version mismatch';
        }

        if ($artifactMetadata->configHash !== $this->configHash) {
            $issues[] = 'config hash mismatch';
        }

        if ($artifactMetadata->environment !== $this->environment) {
            $issues[] = 'environment mismatch';
        }

        if ($artifactMetadata->compileMode !== $this->compileMode) {
            $issues[] = 'compile mode mismatch';
        }

        if ($artifactMetadata->executionMode !== $this->executionMode) {
            $issues[] = 'execution mode mismatch';
        }

        if ($artifactMetadata->pruneMode !== $this->pruneMode) {
            $issues[] = 'prune mode mismatch';
        }

        if ($artifactMetadata->diagnosticsMode !== $this->diagnosticsMode) {
            $issues[] = 'diagnostics mode mismatch';
        }

        if ($artifactMetadata->strict !== $this->strict) {
            $issues[] = 'strict mode mismatch';
        }

        return $issues;
    }

    private function sourceMatchesChecksum(string $path, string $checksum): bool
    {
        if ($checksum === '' || ! is_file(filename: $path)) {
            return false;
        }

        $body = file_get_contents(filename: $path);

        return is_string(value: $body) && sha1(string: $body) === $checksum;
    }

    private function loadCompiledFromPath(string $path): CompiledContainer
    {
        try {
            $loaded = require $path;
        } catch (Throwable $throwable) {
            throw new ContainerException(message: sprintf('Compiled container artifact [%s] could not be loaded.', $path), code: $throwable->getCode(), previous: $throwable);
        }

        if (! $loaded instanceof CompiledContainer) {
            throw new ContainerException(message: sprintf('Compiled container artifact [%s] is invalid.', $path));
        }

        return $loaded;
    }

    private function loadSource(string $source): CompiledContainer
    {
        $directory = $this->cacheDir !== ''
            ? $this->compiledDirectory()
            : sys_get_temp_dir().'/avax-container-runtime';

        if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0o775, recursive: true) && ! is_dir(filename: $directory)) {
            throw new RuntimeException(message: sprintf('Cannot create temporary compiled container directory [%s].', $directory));
        }

        $path = $directory.'/runtime-'.uniqid(prefix: '', more_entropy: true).'.php';
        $body = '<?php'.PHP_EOL.PHP_EOL.$source.PHP_EOL;

        if (file_put_contents(filename: $path, data: $body, flags: LOCK_EX) === false) {
            throw new RuntimeException(message: sprintf('Cannot materialize compiled container source [%s].', $path));
        }

        try {
            return $this->loadCompiledFromPath(path: $path);
        } finally {
            if (is_file(filename: $path)) {
                unlink(filename: $path);
            }
        }
    }

    /**
     * @throws JsonException
     */
    private function write(string $source, ArtifactMetadata $artifactMetadata): void
    {
        $compiledDirectory = $this->compiledDirectory();
        if (! is_dir(filename: $compiledDirectory) && ! mkdir(directory: $compiledDirectory, permissions: 0o775, recursive: true) && ! is_dir(filename: $compiledDirectory)) {
            throw new RuntimeException(message: sprintf('Cannot create compiled container directory [%s].', $compiledDirectory));
        }

        $body = '<?php'.PHP_EOL.PHP_EOL.$source.PHP_EOL;
        $json = json_encode(value: $artifactMetadata->toArray(), flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;

        $this->writeAtomically(path: $this->path(), body: $body);
        $this->writeAtomically(path: $this->metadataPath(), body: $json);

        if (function_exists(function: 'opcache_invalidate')) {
            opcache_invalidate(filename: $this->path(), force: true);
        }
    }

    private function writeAtomically(string $path, string $body): void
    {
        $temp = $path.'.'.uniqid(prefix: 'tmp', more_entropy: true);

        if (file_put_contents(filename: $temp, data: $body, flags: LOCK_EX) === false) {
            throw new RuntimeException(message: sprintf('Cannot write compiled container artifact [%s].', $temp));
        }

        if (! rename(from: $temp, to: $path)) {
            unlink(filename: $temp);

            throw new RuntimeException(message: sprintf('Cannot publish compiled container artifact [%s].', $path));
        }
    }

    /**
     * Loads one compiled container artifact when it is available and fresh.
     *
     * @param  list<string>  $serviceIds
     *
     * @throws ReflectionException
     */
    public function load(array $serviceIds = []): ?CompiledContainer
    {
        if ($this->cacheDir === '') {
            return null;
        }

        $metadata = $this->loadMetadata();
        if (! $metadata instanceof ArtifactMetadata) {
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        $this->artifactMetadata = $metadata;

        $compatibilityIssues = $this->compatibilityIssuesFor(metadata: $metadata);
        if ($compatibilityIssues !== []) {
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_incompatible_total');
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        if (! $this->servicesAreAvailable(serviceIds: $serviceIds, metadata: $metadata)) {
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_misses_total');

            return null;
        }

        if (! $this->sourceMatchesChecksum(path: $this->path(), checksum: $metadata->checksum)) {
            $this->handleCorruption(reason: 'Compiled container checksum validation failed.');

            return null;
        }

        if ($this->validateOnLoad && ! $this->requestedServicesAreFresh(serviceIds: $serviceIds, metadata: $metadata)) {
            $this->resolutionMetrics?->increment(name: 'container_compiled_container_stale_total');

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

        $this->resolutionMetrics?->increment(name: 'container_compiled_container_hits_total');

        return $compiled;
    }

    private function servicesAreAvailable(ArtifactMetadata $artifactMetadata, array $serviceIds): bool
    {
        return $artifactMetadata->includes(serviceIds: $serviceIds);
    }

    /**
     * @throws ReflectionException
     */
    private function requestedServicesAreFresh(ArtifactMetadata $artifactMetadata, array $serviceIds): bool
    {
        $ids = $this->requestedServiceClosure(serviceIds: $serviceIds, metadata: $artifactMetadata);

        foreach ($ids as $id) {
            $current = $this->dependencyCompiler->describe(serviceId: $id);
            if (($artifactMetadata->services[$id]['signature'] ?? null) !== $current['signature'] || ($artifactMetadata->dependencies[$id] ?? []) !== $this->dependenciesForService(serviceId: $id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $serviceIds
     * @return list<string>
     */
    private function requestedServiceClosure(ArtifactMetadata $artifactMetadata, array $serviceIds): array
    {
        $queue = $serviceIds !== []
            ? array_values(array: array_unique(array: $serviceIds))
            : array_keys(array: $artifactMetadata->services);
        $seen = [];

        while ($queue !== []) {
            $serviceId = (string) array_shift(array: $queue);
            if (isset($seen[$serviceId])) {
                continue;
            }

            $seen[$serviceId] = true;
            foreach ($artifactMetadata->dependencies[$serviceId] ?? [] as $dependency) {
                if (! isset($seen[$dependency])) {
                    $queue[] = $dependency;
                }
            }
        }

        $ids = array_keys(array: $seen);
        sort(array: $ids);

        return $ids;
    }

    /**
     * Removes all compiled container artifacts.
     */
    public function flush(): void
    {
        $this->artifactMetadata = null;

        $directory = $this->directory();
        if (! is_dir(filename: $directory)) {
            return;
        }

        $this->deleteDirectory(directory: $directory);
    }

    private function deleteDirectory(string $directory): void
    {
        $files = scandir(directory: $directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.') {
                continue;
            }

            if ($file === '..') {
                continue;
            }

            $path = $directory.'/'.$file;
            if (is_dir(filename: $path)) {
                $this->deleteDirectory(directory: $path);

                continue;
            }

            unlink(filename: $path);
        }

        rmdir(directory: $directory);
    }

    /**
     * Returns whether one service is present in the current compiled metadata.
     */
    public function contains(string $serviceId): bool
    {
        $report = $this->report(serviceIds: [$serviceId]);

        return $report->available && in_array(needle: $serviceId, haystack: $report->entries, strict: true);
    }

    /**
     * Returns the current compile status for the requested service set.
     *
     * @param  list<string>  $serviceIds
     */
    public function report(array $serviceIds = []): CompileReport
    {
        $metadata = $this->reportMetadata();
        $compatibilityIssues = $metadata instanceof ArtifactMetadata
            ? $this->compatibilityIssuesFor(metadata: $metadata)
            : ['compiled metadata is missing'];
        $compatible = $metadata instanceof ArtifactMetadata && $compatibilityIssues === [];
        $freshnessState = $this->freshnessStateFor(serviceIds: $serviceIds, metadata: $metadata);
        $checksumValid = $metadata instanceof ArtifactMetadata
            && ($this->cacheDir === '' || $this->sourceMatchesChecksum(path: $this->path(), checksum: $metadata->checksum));
        $available = $compatible
            && $freshnessState === 'fresh'
            && ($this->cacheDir === '' || is_file(filename: $this->path()))
            && $checksumValid;
        $warnings = $this->warningsFor(
            freshnessState     : $freshnessState,
            compatibilityIssues: $compatibilityIssues,
            checksumValid      : $checksumValid,
            available          : $available,
            metadata           : $metadata,
        );

        return new CompileReport(
            available               : $available,
            compatible              : $compatible,
            freshnessState          : $freshnessState,
            warnings                : $warnings,
            path                    : $this->path(),
            metadataPath            : $this->metadataPath(),
            cacheVersion            : $this->cacheVersion,
            compileMode             : $this->compileMode,
            executionMode           : $metadata?->executionMode ?? $this->executionMode,
            pruneMode               : $metadata?->pruneMode ?? $this->pruneMode,
            environment             : $this->environment,
            fingerprint             : $metadata?->fingerprint ?? '',
            checksumValid           : $checksumValid,
            totalServices           : count(value: $metadata?->services ?? []),
            compiledServicesCount   : count(value: $metadata?->entries ?? []),
            reusedServicesCount     : (int) ($metadata?->statistics['reusedServices'] ?? 0),
            invalidatedServicesCount: count(value: $metadata?->invalidatedServices ?? []),
            deferredServicesCount   : (int) ($metadata?->statistics['deferredServices'] ?? 0),
            lazyServicesCount       : (int) ($metadata?->statistics['lazyServices'] ?? 0),
            tagIndexSize            : count(value: $metadata?->tags ?? []),
            aliasMapSize            : count(value: $metadata?->aliases ?? []),
            decorationMapSize       : count(value: array_filter(
                array   : $metadata?->decorations ?? [],
                callback: static fn (int $count): bool => $count > 0,
            )),
            providerBootPlanSize    : (int) ($metadata?->statistics['providerBootPlanSize'] ?? 0),
            lifetimePlanSummary     : $this->lifetimePlanSummaryFor(metadata: $metadata),
            entries                 : $metadata?->entryIds() ?? [],
            changedServices         : $metadata?->changedServices ?? [],
            invalidatedServices     : $metadata?->invalidatedServices ?? [],
            validationIssues        : $metadata?->validationIssues ?? [],
            compatibilityIssues     : $compatible ? [] : $compatibilityIssues,
            invalidationReasons     : $metadata?->invalidationReasons ?? [],
            statistics              : $metadata?->statistics ?? [],
            pruning                 : $metadata?->pruning ?? [
                'mode' => $this->pruneMode,
                'rootServices' => [],
                'prunedServices' => [],
                'reasons' => [],
            ],
            metadata                : $metadata,
        );
    }

    private function reportMetadata(): ?ArtifactMetadata
    {
        return $this->loadMetadata(quarantineOnFailure: false) ?? $this->artifactMetadata;
    }

    /**
     * @param  list<string>  $serviceIds
     *
     * @throws ReflectionException
     */
    private function freshnessStateFor(?ArtifactMetadata $artifactMetadata, array $serviceIds): string
    {
        if (! $artifactMetadata instanceof ArtifactMetadata) {
            return 'missing';
        }

        if ($this->compatibilityIssuesFor(metadata: $artifactMetadata) !== []) {
            return 'incompatible';
        }

        if (! $this->servicesAreAvailable(serviceIds: $serviceIds, metadata: $artifactMetadata)) {
            return 'partial';
        }

        if ($this->cacheDir === '') {
            return 'fresh';
        }

        if (! $this->sourceMatchesChecksum(path: $this->path(), checksum: $artifactMetadata->checksum)) {
            return 'corrupt';
        }

        if ($this->validateOnLoad && ! $this->requestedServicesAreFresh(serviceIds: $serviceIds, metadata: $artifactMetadata)) {
            return 'stale';
        }

        return 'fresh';
    }

    /**
     * @param  list<string>  $compatibilityIssues
     * @return list<string>
     */
    private function warningsFor(
        ?ArtifactMetadata $artifactMetadata,
        string $freshnessState,
        array $compatibilityIssues,
        bool $checksumValid,
        bool $available,
    ): array {
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

        if (! $checksumValid && $artifactMetadata instanceof ArtifactMetadata) {
            $warnings[] = 'compiled artifact checksum is invalid';
        }

        foreach ($artifactMetadata?->validationIssues ?? [] as $issue) {
            $warnings[] = $issue;
        }

        $warnings = array_values(array: array_unique(array: $warnings));
        sort(array: $warnings);

        return $warnings;
    }

    /**
     * @return array<string, int>
     */
    private function lifetimePlanSummaryFor(?ArtifactMetadata $artifactMetadata): array
    {
        $summary = [];

        foreach ($artifactMetadata?->lifetimes ?? [] as $plan) {
            $name = (string) ($plan['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $summary[$name] = ($summary[$name] ?? 0) + 1;
        }

        ksort(array: $summary);

        return $summary;
    }
}
