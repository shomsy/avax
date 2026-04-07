<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Engine;

use Avax\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capability\Observability\Metrics\CollectMetrics;
use Avax\Container\DependencyInjection\Capability\Observability\Trace\ResolutionTrace;
use Avax\Container\DependencyInjection\Capability\Observability\Trace\TraceObserverInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionExceptionWithTrace;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionStageHandlers;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionState;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionStateMachine;
use Avax\Container\DependencyInjection\Capability\Scopes\ScopeRegistry;
use Closure;
use Throwable;

/**
 * Shared engine that turns one resolution request into a value or object.
 */
final class ResolutionEngine implements EngineInterface
{
    private ContainerRuntimeInterface|null $container = null;

    public function __construct(
        private readonly DependencyResolver $resolver,
        private readonly Instantiator       $instantiator,
        private readonly DefinitionStore    $store,
        private readonly ScopeRegistry      $registry,
        private readonly CollectMetrics     $metrics,
        ContainerRuntimeInterface|null      $container = null
    ) {
        $this->container = $container;
    }

    public function setContainer(ContainerRuntimeInterface $container) : void
    {
        if ($this->container !== null) {
            throw new ContainerException(message: 'Container already initialized on engine.');
        }

        $this->container = $container;
    }

    public function hasInternals() : bool
    {
        return $this->container !== null;
    }

    public function resolve(KernelContext $context, TraceObserverInterface|null $traceObserver = null) : mixed
    {
        if ($this->container === null) {
            throw new ContainerException(
                message: 'Container engine is not fully initialized. Call setContainer() before resolution.'
            );
        }

        $trace        = new ResolutionTrace;
        $stateMachine = new ResolutionStateMachine;

        try {
            [$result, $finalTrace] = $this->resolveFromBindings(
                context     : $context,
                stateMachine: $stateMachine,
                trace       : $trace
            );
            $this->recordTrace(observer: $traceObserver, trace: $finalTrace);

            return $result;
        } catch (Throwable $exception) {
            $finalTrace = $trace;
            $metaTrace  = $context->getMeta(namespace: 'resolution', key: 'trace', default: null);
            if (is_array($metaTrace)) {
                $finalTrace = ResolutionTrace::fromArray(entries: $metaTrace);
            }

            $this->recordTrace(observer: $traceObserver, trace: $finalTrace);
            throw $exception;
        }
    }

    private function resolveFromBindings(
        KernelContext $context,
        ResolutionStateMachine $stateMachine,
        ResolutionTrace $trace
    ) : array {
        $candidate = null;
        $handlers  = new ResolutionStageHandlers(handlers: [
            ResolutionState::ContextualLookup->value => fn(KernelContext $context) : mixed => $this->resolveContextualBinding(context: $context),
            ResolutionState::DefinitionLookup->value => fn(KernelContext $context) : mixed => $this->resolveDefinitionBinding(context: $context),
            ResolutionState::Autowire->value         => function (KernelContext $context) use (&$candidate) : mixed {
                return $this->resolveAutowireCandidate(context: $context, current: $candidate);
            },
            ResolutionState::Evaluate->value         => function (KernelContext $context) use (&$candidate) : mixed {
                return $this->evaluateCandidate(candidate: $candidate, context: $context);
            },
            ResolutionState::Instantiate->value      => function (KernelContext $context) use (&$candidate) : mixed {
                return $this->instantiateCandidate(candidate: $candidate, context: $context);
            },
        ]);

        $trace = $trace->record(
            state  : $stateMachine->state(),
            stage  : ResolutionState::ContextualLookup->value,
            outcome: 'start'
        );

        foreach ([ResolutionState::ContextualLookup, ResolutionState::DefinitionLookup, ResolutionState::Autowire] as $state) {
            $handler  = $handlers->get(state: $state);
            $resolved = $handler($context);

            if ($resolved !== null) {
                $candidate = $resolved;
            }

            $trace = $trace->record(
                state  : $state,
                stage  : $state->value,
                outcome: $resolved === null ? 'miss' : 'hit'
            );

            $nextState = $handlers->nextStateAfter(state: $state) ?? ResolutionState::NotFound;

            if ($state === ResolutionState::Autowire && $candidate === null) {
                $stateMachine->advanceTo(next: ResolutionState::NotFound, hit: false);
                break;
            }

            if ($state === ResolutionState::Autowire && $candidate !== null) {
                $nextState = ResolutionState::Evaluate;
            }

            $stateMachine->advanceTo(next: $nextState, hit: $resolved !== null || $candidate !== null);

            if ($nextState === ResolutionState::NotFound) {
                break;
            }
        }

        if ($candidate === null) {
            $trace = $trace->record(state: ResolutionState::NotFound, stage: 'terminal', outcome: 'not_found');
            $context->setMeta(namespace: 'resolution', key: 'trace', value: $trace->toArray());

            $traceString = json_encode($trace->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            throw new ResolutionExceptionWithTrace(
                trace  : $trace,
                message: sprintf(
                    'Service [%s] not found in container.%s',
                    $context->serviceId,
                    $traceString === false ? '' : sprintf(' Trace: %s', $traceString)
                )
            );
        }

        if ($stateMachine->state() !== ResolutionState::Evaluate) {
            $stateMachine->advanceTo(next: ResolutionState::Evaluate, hit: true);
        }

        $evaluateHandler = $handlers->get(state: ResolutionState::Evaluate);
        $evaluated       = $evaluateHandler($context);
        $candidate       = $evaluated;
        $trace           = $trace->record(
            state  : ResolutionState::Evaluate,
            stage  : ResolutionState::Evaluate->value,
            outcome: $evaluated === null ? 'miss' : 'hit'
        );

        $stateMachine->advanceTo(next: ResolutionState::Instantiate, hit: $evaluated !== null);

        $instantiateHandler = $handlers->get(state: ResolutionState::Instantiate);
        $final              = $instantiateHandler($context);
        $trace              = $trace->record(
            state  : ResolutionState::Instantiate,
            stage  : ResolutionState::Instantiate->value,
            outcome: $final === null ? 'miss' : 'hit'
        );

        if ($final === null) {
            $stateMachine->advanceTo(next: ResolutionState::NotFound, hit: false);
            $trace = $trace->record(state: ResolutionState::NotFound, stage: 'terminal', outcome: 'not_found');
            $context->setMeta(namespace: 'resolution', key: 'trace', value: $trace->toArray());

            throw new ResolutionExceptionWithTrace(
                trace  : $trace,
                message: sprintf('Service [%s] not found in container.', $context->serviceId)
            );
        }

        $stateMachine->advanceTo(next: ResolutionState::Success, hit: true);
        $trace = $trace->record(state: ResolutionState::Success, stage: 'terminal', outcome: 'success');
        $context->setMeta(namespace: 'resolution', key: 'trace', value: $trace->toArray());

        return [$final, $trace];
    }

    private function resolveContextualBinding(KernelContext $context) : mixed
    {
        if ($context->parent === null) {
            return null;
        }

        return $this->store->getContextualMatch(
            consumer: $context->parent->serviceId,
            needs   : $context->serviceId
        );
    }

    private function resolveDefinitionBinding(KernelContext $context) : mixed
    {
        return $this->store->get(abstract: $context->serviceId)?->concrete;
    }

    private function resolveAutowireCandidate(KernelContext $context, mixed &$current) : mixed
    {
        if ($current !== null) {
            return $current;
        }

        if (! class_exists(class: $context->serviceId)) {
            return null;
        }

        return $context->serviceId;
    }

    private function evaluateCandidate(mixed $candidate, KernelContext $context) : mixed
    {
        if ($candidate === null) {
            return null;
        }

        if (is_object($candidate) && ! ($candidate instanceof Closure)) {
            return $candidate;
        }

        if ($candidate instanceof Closure) {
            return $candidate($this->container, $context->overrides);
        }

        if (is_string($candidate)) {
            if ($candidate !== $context->serviceId) {
                return $this->container?->resolveContext(context: $context->child(serviceId: $candidate));
            }

            return $candidate;
        }

        return $candidate;
    }

    private function instantiateCandidate(mixed $candidate, KernelContext $context) : mixed
    {
        if (is_string($candidate)) {
            return $this->instantiator->build(
                class    : $candidate,
                container: $this->container,
                overrides: $context->overrides,
                context  : $context
            );
        }

        return $candidate;
    }

    private function recordTrace(TraceObserverInterface|null $observer, ResolutionTrace $trace) : void
    {
        if ($observer === null) {
            return;
        }

        $observer->record(trace: $trace);
    }
}
