<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileReport;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use JsonException;
use ReflectionException;

/**
 * Owns compiled runtime attachment, refresh, and hot-path decisions.
 */
final class CompiledRuntime
{
    private int $compiledRevision = -1;

    private readonly HotPathInliner $hotPathInliner;

    public function __construct(
        private readonly ?CompileContainer $compileContainer = null, HotPathInliner|null $hotPathInliner = null,
        private readonly ?ResolutionMetrics $resolutionMetrics = null,
        private readonly string $executionMode = CreateContainerConfig::EXECUTION_MODE_COMPILED,
    ) {
        $hotPathInliner ??= new HotPathInliner();
        $this->hotPathInliner = $hotPathInliner;
    }

    public function compiledRevision(): int
    {
        return $this->compiledRevision;
    }

    public function flush(): void
    {
        $this->compileContainer?->flush();
        $this->reset();
    }

    public function reset(): void
    {
        $this->hotPathInliner->detach();
        $this->compiledRevision = -1;
    }

    public function shouldValidateBeforeCompile(): bool
    {
        return $this->compileContainer?->shouldValidateBeforeCompile() ?? false;
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function compile(array|null $serviceIds = null, array|null $validationIssues = null, bool $warmed = false) : CompiledContainer|null
    {
        $serviceIds ??= [];
        $validationIssues ??= [];

        return $this->compileContainer?->compile(
            serviceIds      : $serviceIds,
            validationIssues: $validationIssues,
            warmed          : $warmed,
        );
    }

    public function isWarmedUp(): bool
    {
        if ($this->hotPathInliner->isAttached()) {
            return true;
        }

        return $this->compileContainer?->report()->available ?? false;
    }

    public function isAttached(): bool
    {
        return $this->hotPathInliner->isAttached();
    }

    public function report(array $serviceIds = []) : CompileReport|null
    {
        return $this->compileContainer?->report(serviceIds: $serviceIds);
    }

    /**
     * @throws ReflectionException
     */
    public function isCompiled(DependencyRegistry $dependencyRegistry, string $serviceId): bool
    {
        $this->refresh(serviceId: $serviceId, registrations: $dependencyRegistry);
        if ($this->hotPathInliner->has(serviceId: $serviceId)) {
            return true;
        }

        return $this->compileContainer?->contains(serviceId: $serviceId) ?? false;
    }

    /**
     * @throws ReflectionException
     */
    public function refresh(DependencyRegistry $dependencyRegistry, string|null $serviceId = null) : void
    {
        if ($this->executionMode === CreateContainerConfig::EXECUTION_MODE_DYNAMIC) {
            $this->hotPathInliner->detach();
            $this->compiledRevision = $dependencyRegistry->revision();

            return;
        }

        if (! $this->compileContainer instanceof CompileContainer) {
            return;
        }

        $revision = $dependencyRegistry->revision();
        if ($revision === $this->compiledRevision && $this->hotPathInliner->isAttached()) {
            return;
        }

        $compiled = $this->compileContainer->load(
            serviceIds: $serviceId !== null ? [$serviceId] : [],
        );
        if ($compiled instanceof CompiledContainer) {
            $this->hotPathInliner->attach(compiled: $compiled);
        } else {
            $artifactAvailable = $this->compileContainer->report()->available;
            $requestedIsCompiled = $serviceId !== null && $this->compileContainer->contains(serviceId: $serviceId);

            if (! $artifactAvailable || $requestedIsCompiled) {
                $this->hotPathInliner->detach();
            }
        }

        if (! $this->hotPathInliner->isAttached() && ! $this->compileContainer->report()->available) {
            $this->hotPathInliner->detach();
        }

        $this->compiledRevision = $revision;
    }

    public function attach(CompiledContainer $compiledContainer, int $revision): void
    {
        $this->hotPathInliner->attach(compiled: $compiledContainer);
        $this->compiledRevision = $revision;
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $report = $this->compileContainer?->report();
        $inlinerState = $this->hotPathInliner->state();

        return [
            'attached' => $this->hotPathInliner->isAttached(),
            'entryCount' => $inlinerState['entryCount'],
            'artifactAvailable' => $report?->available ?? false,
            'compatible' => $report?->compatible ?? false,
            'freshnessState' => $report?->freshnessState ?? 'missing',
            'executionMode' => $report?->executionMode ?? $this->executionMode,
            'reason' => $inlinerState['reason'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function state(DependencyRegistry $dependencyRegistry, string $serviceId): array
    {
        $this->refresh(serviceId: $serviceId, registrations: $dependencyRegistry);
        $report = $this->compileContainer?->report(serviceIds: [$serviceId]);
        $decision = $this->decision(
            registrations: $dependencyRegistry,
            request      : new ResolveRequest(serviceId: $serviceId),
        );
        $inlinerState = $this->hotPathInliner->state(serviceId: $serviceId);

        return [
            'attached' => $inlinerState['attached'],
            'entryAttached' => $this->hotPathInliner->has(serviceId: $serviceId),
            'entryCount' => $inlinerState['entryCount'],
            'containsEntry' => $this->compileContainer?->contains(serviceId: $serviceId) ?? false,
            'artifactAvailable' => $report?->available ?? false,
            'compatible' => $report?->compatible ?? false,
            'compatibilityIssues' => $report?->compatibilityIssues ?? [],
            'freshnessState' => $report?->freshnessState ?? 'missing',
            'compileMode' => $report?->compileMode ?? '',
            'executionMode' => $report?->executionMode ?? $this->executionMode,
            'decision' => $decision['decision'],
            'reason' => $decision['reason'],
            'hotPath' => $inlinerState,
        ];
    }

    /**
     * @return array{useCompiled: bool, decision: string, reason: string}
     */
    public function decision(DependencyRegistry $dependencyRegistry, ResolveRequest $resolveRequest): array
    {
        $this->refresh(serviceId: $resolveRequest->serviceId, registrations: $dependencyRegistry);
        $report = $this->compileContainer?->report(serviceIds: [$resolveRequest->serviceId]);

        if (! $this->compileContainer instanceof CompileContainer) {
            return [
                'useCompiled' => false,
                'decision' => 'dynamic',
                'reason' => 'compiled runtime is not configured',
            ];
        }

        if ($this->executionMode === CreateContainerConfig::EXECUTION_MODE_DYNAMIC) {
            return [
                'useCompiled' => false,
                'decision' => 'dynamic',
                'reason' => 'execution mode is dynamic',
            ];
        }

        if (! ($report?->available ?? false)) {
            $state = $report?->freshnessState ?? 'missing';

            return [
                'useCompiled' => false,
                'decision' => 'dynamic',
                'reason' => match ($state) {
                    'missing' => 'compiled artifact is missing',
                    'incompatible' => 'compiled artifact is incompatible with the current runtime',
                    'partial' => 'compiled artifact does not contain the requested entry',
                    'corrupt' => 'compiled artifact is corrupt',
                    'stale' => 'compiled artifact is stale',
                    default => 'compiled artifact is unavailable',
                },
            ];
        }

        if (! $this->hotPathInliner->has(serviceId: $resolveRequest->serviceId)) {
            return [
                'useCompiled' => false,
                'decision' => 'dynamic',
                'reason' => 'compiled runtime is attached but the requested entry is missing',
            ];
        }

        $consumer = $resolveRequest->parent?->serviceId ?? $resolveRequest->consumer;
        if (
            $consumer !== null
            && $dependencyRegistry->getContextualMatch(consumer: $consumer, needs: $resolveRequest->serviceId) !== null
        ) {
            return [
                'useCompiled' => false,
                'decision' => 'dynamic',
                'reason' => 'contextual binding overrides the compiled path',
            ];
        }

        return [
            'useCompiled' => true,
            'decision' => $this->executionMode === CreateContainerConfig::EXECUTION_MODE_GENERATED
                ? 'generated'
                : 'compiled',
            'reason' => $this->executionMode === CreateContainerConfig::EXECUTION_MODE_GENERATED
                ? 'generated execution path is attached and usable'
                : 'compiled hot path is attached and usable',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function shouldUse(DependencyRegistry $dependencyRegistry, ResolveRequest $resolveRequest): bool
    {
        if ($this->executionMode === CreateContainerConfig::EXECUTION_MODE_DYNAMIC) {
            return false;
        }

        $this->refresh(serviceId: $resolveRequest->serviceId, registrations: $dependencyRegistry);

        if (! $this->hotPathInliner->has(serviceId: $resolveRequest->serviceId)) {
            return false;
        }

        $consumer = $resolveRequest->parent?->serviceId ?? $resolveRequest->consumer;
        if ($consumer === null) {
            return true;
        }

        return $dependencyRegistry->getContextualMatch(
            consumer: $consumer,
            needs   : $resolveRequest->serviceId,
        ) === null;
    }

    public function resolve(ResolveDependency $resolveDependency, ResolveRequest $resolveRequest): mixed
    {
        $this->resolutionMetrics?->increment(name: 'container_compiled_container_resolve_total');

        return $this->hotPathInliner->resolve(
            serviceId: $resolveRequest->serviceId,
            resolver : $resolveDependency,
            request  : $resolveRequest,
        );
    }
}
