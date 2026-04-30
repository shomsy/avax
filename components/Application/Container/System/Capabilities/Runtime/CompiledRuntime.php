<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileContainer;
use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileReport;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use JsonException;
use ReflectionException;

/**
 * Owns compiled runtime attachment, refresh, and hot-path decisions.
 */
final class CompiledRuntime
{
    private int                            $compiledRevision = -1;
    private readonly string                $executionMode;
    private readonly ResolutionMetrics|null $metrics;
    private readonly HotPathInliner        $inliner;
    private readonly CompileContainer|null $compiler;

    public function __construct(
        CompileContainer  $compiler = null,
        HotPathInliner    $inliner = null,
        ResolutionMetrics $metrics = null,
        string            $executionMode = CreateContainerConfig::EXECUTION_MODE_COMPILED,
    )
    {
        $inliner ??= new HotPathInliner();
        $this->compiler      = $compiler;
        $this->inliner       = $inliner;
        $this->metrics       = $metrics;
        $this->executionMode = $executionMode;
    }

    public function compiledRevision() : int
    {
        return $this->compiledRevision;
    }

    public function flush() : void
    {
        $this->compiler?->flush();
        $this->reset();
    }

    public function reset() : void
    {
        $this->inliner->detach();
        $this->compiledRevision = -1;
    }

    public function shouldValidateBeforeCompile() : bool
    {
        return $this->compiler?->shouldValidateBeforeCompile() ?? false;
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function compile(array $serviceIds = null, array $validationIssues = null, bool $warmed = false) : CompiledContainer|null
    {
        $serviceIds       ??= [];
        $validationIssues ??= [];

        return $this->compiler?->compile(
            serviceIds      : $serviceIds,
            validationIssues: $validationIssues,
            warmed          : $warmed,
        );
    }

    public function isWarmedUp() : bool
    {
        if ($this->inliner->isAttached()) {
            return true;
        }

        return $this->compiler?->report()->available ?? false;
    }

    public function isAttached() : bool
    {
        return $this->inliner->isAttached();
    }

    public function report(array $serviceIds = []) : CompileReport|null
    {
        return $this->compiler?->report(serviceIds: $serviceIds);
    }

    /**
     * @throws ReflectionException
     */
    public function isCompiled(ServiceRegistry $registrations, string $serviceId) : bool
    {
        $this->refresh(registrations: $registrations, serviceId: $serviceId);

        return $this->inliner->has(serviceId: $serviceId)
            || ($this->compiler?->contains(serviceId: $serviceId) ?? false);
    }

    /**
     * @throws ReflectionException
     */
    public function refresh(ServiceRegistry $registrations, string $serviceId = null) : void
    {
        if ($this->executionMode === CreateContainerConfig::EXECUTION_MODE_DYNAMIC) {
            $this->inliner->detach();
            $this->compiledRevision = $registrations->revision();

            return;
        }

        if ($this->compiler === null) {
            return;
        }

        $revision = $registrations->revision();
        if ($revision === $this->compiledRevision && $this->inliner->isAttached()) {
            return;
        }

        $compiled = $this->compiler->load(
            serviceIds: $serviceId !== null ? [$serviceId] : [],
        );
        if ($compiled !== null) {
            $this->inliner->attach(compiled: $compiled);
        } else {
            $artifactAvailable   = $this->compiler->report()->available;
            $requestedIsCompiled = $serviceId !== null && $this->compiler->contains(serviceId: $serviceId);

            if (! $artifactAvailable || $requestedIsCompiled) {
                $this->inliner->detach();
            }
        }

        if (! $this->inliner->isAttached() && ! $this->compiler->report()->available) {
            $this->inliner->detach();
        }

        $this->compiledRevision = $revision;
    }

    public function attach(CompiledContainer $compiled, int $revision) : void
    {
        $this->inliner->attach(compiled: $compiled);
        $this->compiledRevision = $revision;
    }

    /**
     * @return array<string, mixed>
     */
    public function summary() : array
    {
        $report       = $this->compiler?->report();
        $inlinerState = $this->inliner->state();

        return [
            'attached'          => $this->inliner->isAttached(),
            'entryCount'        => $inlinerState['entryCount'],
            'artifactAvailable' => $report?->available ?? false,
            'compatible'        => $report?->compatible ?? false,
            'freshnessState'    => $report?->freshnessState ?? 'missing',
            'executionMode'     => $report?->executionMode ?? $this->executionMode,
            'reason'            => $inlinerState['reason'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function state(ServiceRegistry $registrations, string $serviceId) : array
    {
        $this->refresh(registrations: $registrations, serviceId: $serviceId);
        $report   = $this->compiler?->report(serviceIds: [$serviceId]);
        $decision = $this->decision(
            registrations: $registrations,
            request      : new ResolveRequest(serviceId: $serviceId),
        );
        $inlinerState = $this->inliner->state(serviceId: $serviceId);

        return [
            'attached'            => $inlinerState['attached'],
            'entryAttached'       => $this->inliner->has(serviceId: $serviceId),
            'entryCount'          => $inlinerState['entryCount'],
            'containsEntry'       => $this->compiler?->contains(serviceId: $serviceId) ?? false,
            'artifactAvailable'   => $report?->available ?? false,
            'compatible'          => $report?->compatible ?? false,
            'compatibilityIssues' => $report?->compatibilityIssues ?? [],
            'freshnessState'      => $report?->freshnessState ?? 'missing',
            'compileMode'         => $report?->compileMode ?? '',
            'executionMode'       => $report?->executionMode ?? $this->executionMode,
            'decision'            => $decision['decision'],
            'reason'              => $decision['reason'],
            'hotPath'             => $inlinerState,
        ];
    }

    /**
     * @return array{useCompiled: bool, decision: string, reason: string}
     */
    public function decision(ServiceRegistry $registrations, ResolveRequest $request) : array
    {
        $this->refresh(registrations: $registrations, serviceId: $request->serviceId);
        $report = $this->compiler?->report(serviceIds: [$request->serviceId]);

        if ($this->compiler === null) {
            return [
                'useCompiled' => false,
                'decision'    => 'dynamic',
                'reason'      => 'compiled runtime is not configured',
            ];
        }

        if ($this->executionMode === CreateContainerConfig::EXECUTION_MODE_DYNAMIC) {
            return [
                'useCompiled' => false,
                'decision'    => 'dynamic',
                'reason'      => 'execution mode is dynamic',
            ];
        }

        if (! ($report?->available ?? false)) {
            $state = $report?->freshnessState ?? 'missing';

            return [
                'useCompiled' => false,
                'decision'    => 'dynamic',
                'reason'      => match ($state) {
                    'missing'      => 'compiled artifact is missing',
                    'incompatible' => 'compiled artifact is incompatible with the current runtime',
                    'partial'      => 'compiled artifact does not contain the requested entry',
                    'corrupt'      => 'compiled artifact is corrupt',
                    'stale'        => 'compiled artifact is stale',
                    default        => 'compiled artifact is unavailable',
                },
            ];
        }

        if (! $this->inliner->has(serviceId: $request->serviceId)) {
            return [
                'useCompiled' => false,
                'decision'    => 'dynamic',
                'reason'      => 'compiled runtime is attached but the requested entry is missing',
            ];
        }

        $consumer = $request->parent?->serviceId ?? $request->consumer;
        if (
            $consumer !== null
            && $registrations->getContextualMatch(consumer: $consumer, needs: $request->serviceId) !== null
        ) {
            return [
                'useCompiled' => false,
                'decision'    => 'dynamic',
                'reason'      => 'contextual binding overrides the compiled path',
            ];
        }

        return [
            'useCompiled' => true,
            'decision'    => $this->executionMode === CreateContainerConfig::EXECUTION_MODE_GENERATED
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
    public function shouldUse(ServiceRegistry $registrations, ResolveRequest $request) : bool
    {
        if ($this->executionMode === CreateContainerConfig::EXECUTION_MODE_DYNAMIC) {
            return false;
        }

        $this->refresh(registrations: $registrations, serviceId: $request->serviceId);

        if (! $this->inliner->has(serviceId: $request->serviceId)) {
            return false;
        }

        $consumer = $request->parent?->serviceId ?? $request->consumer;
        if ($consumer === null) {
            return true;
        }

        return $registrations->getContextualMatch(
                consumer: $consumer,
                needs   : $request->serviceId,
            ) === null;
    }

    public function resolve(ServiceResolver $resolver, ResolveRequest $request) : mixed
    {
        $this->metrics?->increment(name: 'container_compiled_container_resolve_total');

        return $this->inliner->resolve(
            serviceId: $request->serviceId,
            resolver : $resolver,
            request  : $request,
        );
    }
}
