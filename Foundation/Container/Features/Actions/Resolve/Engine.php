<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\ResolutionPipelineController;
use Avax\Container\Core\Kernel\ResolutionState;
use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Core\Exceptions\ResolutionExceptionWithTrace;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Avax\Container\Observe\Trace\ResolutionTrace;
use Avax\Container\Observe\Trace\TraceObserverInterface;
use Closure;
use Throwable;

/**
 * The core resolution engine for determining and producing service instances.
 *
 * This engine acts as the "Fulfillment Orchestrator". It evaluates service requests
 * by checking contextual rules, explicit bindings, and fallback autowiring.
 * It manages the delegation of construction to the {@see Instantiator} while
 * maintaining the integrity of the resolution context (parent chains, depth, loops).
 *
 * @see     docs/Features/Actions/Resolve/Engine.md
 */
final class Engine implements EngineInterface
{
    /** @var ContainerInternalInterface|null The container facade used for nested resolutions. */
    private ContainerInternalInterface|null $container = null;

    /**
     * Initializes the engine with essential collaborators.
     *
     * @param DependencyResolverInterface $resolver     Resolver for constructor and method parameters.
     * @param Instantiator                $instantiator The component that handles physical object creation.
     * @param DefinitionStore             $store        Central registry of service blueprints.
     * @param ScopeRegistry               $registry     Storage for shared (singleton/scoped) instances.
     * @param CollectMetrics              $metrics      Collector for performance and observability data.
     */
    public function __construct(
        private readonly DependencyResolverInterface $resolver,
        private readonly Instantiator                $instantiator,
        private readonly DefinitionStore             $store,
        private readonly ScopeRegistry               $registry,
        private readonly CollectMetrics              $metrics,
        ContainerInternalInterface|null              $container = null
    )
    {
        $this->container = $container;
    }

    /**
     * Wire the container facade into the engine and its collaborators.
     *
     * @param ContainerInternalInterface $container The application container instance.
     *
     * @throws ContainerException When attempting to initialize more than once.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-setcontainer
     */
    public function setContainer(ContainerInternalInterface $container) : void
    {
        if ($this->container !== null) {
            throw new ContainerException(message: 'Container already initialized on engine.');
        }

        $this->container = $container;
    }

    /**
     * Check if the engine has all required internal state to operate.
     *
     * @return bool True if the container reference is initialized.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-hasinternals
     */
    public function hasInternals() : bool
    {
        return $this->container !== null;
    }

    /**
     * Resolve a service into an instance or value based on the provided context.
     *
     * @param KernelContext               $context       The resolution context containing ID and overrides.
     * @param TraceObserverInterface|null $traceObserver Optional trace sink.
     *
     * @return mixed The fully resolved service instance.
     *
     * @throws \Throwable
     * @see docs/Features/Actions/Resolve/Engine.md#method-resolve
     */
    public function resolve(KernelContext $context, TraceObserverInterface|null $traceObserver = null) : mixed
    {
        if ($this->container === null) {
            throw new ContainerException(message: 'Container engine is not fully initialized. Call setContainer() before resolution.');
        }

        $trace      = new ResolutionTrace;
        $controller = new ResolutionPipelineController;

        try {
            [$result, $finalTrace] = $this->resolveFromBindings(context: $context, controller: $controller, trace: $trace);
            $this->recordTrace(observer: $traceObserver, trace: $finalTrace);

            return $result;
        } catch (Throwable $e) {
            $finalTrace = $trace;
            $metaTrace  = $context->getMeta(namespace: 'resolution', key: 'trace', default: null);
            if (is_array($metaTrace)) {
                $finalTrace = ResolutionTrace::fromArray(entries: $metaTrace);
            }
            $this->recordTrace(observer: $traceObserver, trace: $finalTrace);
            throw $e;
        }
    }

    /**
     * Internal logic that prioritizes bindings (Contextual > Explicit > Autowire).
     *
     * Records a resolution trace into the context for diagnostics.
     *
     * @param KernelContext $context The resolution context.
     *
     * @return array{mixed, ResolutionTrace} The resolved value and final trace.
     *
     * @throws ResolutionExceptionWithTrace When no binding or autowire path can satisfy the request.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-resolvefrombindings
     */
    private function resolveFromBindings(KernelContext $context, ResolutionPipelineController $controller, ResolutionTrace $trace) : array
    {
        $candidate = null;
        $handlers  = new ResolutionStageHandlerMap(handlers: [
            ResolutionState::ContextualLookup->value => fn(KernelContext $context) : mixed => $this->resolveContextualBinding(context: $context),
            ResolutionState::DefinitionLookup->value => fn(KernelContext $context) : mixed => $this->resolveDefinitionBinding(context: $context),
            ResolutionState::Autowire->value         => function (KernelContext $context) use (&$candidate) {
                return $this->resolveAutowireCandidate(context: $context, current: $candidate);
            },
            ResolutionState::Evaluate->value         => function (KernelContext $context) use (&$candidate) {
                return $this->evaluateCandidate(candidate: $candidate, context: $context);
            },
            ResolutionState::Instantiate->value      => function (KernelContext $context) use (&$candidate) {
                return $this->instantiateCandidate(candidate: $candidate, context: $context);
            },
        ]);

        $trace = $trace->record(state: $controller->state(), stage: ResolutionState::ContextualLookup->value, outcome: 'start');

        $discoveryStates = [
            ResolutionState::ContextualLookup,
            ResolutionState::DefinitionLookup,
            ResolutionState::Autowire,
        ];

        foreach ($discoveryStates as $state) {
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

            // If autowire missed and no candidate, terminal not found.
            if ($state === ResolutionState::Autowire && $candidate === null) {
                $controller->advanceTo(next: ResolutionState::NotFound, hit: false);
                break;
            }

            if ($state === ResolutionState::Autowire && $candidate !== null) {
                $nextState = ResolutionState::Evaluate;
            }

            $controller->advanceTo(next: $nextState, hit: $resolved !== null || $candidate !== null);

            if ($nextState === ResolutionState::NotFound) {
                break;
            }
        }

        if ($candidate === null) {
            $trace = $trace->record(state: ResolutionState::NotFound, stage: 'terminal', outcome: 'not_found');
            $context->setMeta(namespace: 'resolution', key: 'trace', value: $trace->toArray());
            $traceString = json_encode(value: $trace->toArray(), flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            throw new ResolutionExceptionWithTrace(
                trace  : $trace,
                message: sprintf(
                    'Service [%s] not found in container.%s',
                    $context->serviceId,
                    $traceString === false ? '' : sprintf(' Trace: %s', $traceString)
                )
            );
        }

        if ($controller->state() !== ResolutionState::Evaluate) {
            $controller->advanceTo(next: ResolutionState::Evaluate, hit: true);
        }

        $evaluateHandler = $handlers->get(state: ResolutionState::Evaluate);
        $evaluated       = $evaluateHandler($context);
        $candidate       = $evaluated;
        $trace           = $trace->record(state: ResolutionState::Evaluate, stage: ResolutionState::Evaluate->value, outcome: $evaluated === null ? 'miss' : 'hit');
        $controller->advanceTo(next: ResolutionState::Instantiate, hit: $evaluated !== null);

        $instantiateHandler = $handlers->get(state: ResolutionState::Instantiate);
        $final              = $instantiateHandler($context);
        $trace              = $trace->record(state: ResolutionState::Instantiate, stage: ResolutionState::Instantiate->value, outcome: $final === null ? 'miss' : 'hit');
        if ($final === null) {
            $controller->advanceTo(next: ResolutionState::NotFound, hit: false);
            $trace = $trace->record(state: ResolutionState::NotFound, stage: 'terminal', outcome: 'not_found');
            $context->setMeta(namespace: 'resolution', key: 'trace', value: $trace->toArray());
            throw new ResolutionExceptionWithTrace(
                trace  : $trace,
                message: sprintf('Service [%s] not found in container.', $context->serviceId)
            );
        }

        $controller->advanceTo(next: ResolutionState::Success, hit: true);

        $trace = $trace->record(state: ResolutionState::Success, stage: 'terminal', outcome: 'success');
        $context->setMeta(namespace: 'resolution', key: 'trace', value: $trace->toArray());

        return [$final, $trace];
    }

    /**
     * Attempt to resolve a contextual binding for the current consumer chain.
     *
     * @param KernelContext $context The resolution context.
     *
     * @return mixed|null Resolved value when contextual binding exists, null otherwise.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-resolvecontextualbinding
     */
    private function resolveContextualBinding(KernelContext $context) : mixed
    {
        if ($context->parent === null) {
            return null;
        }

        $contextual = $this->store->getContextualMatch(consumer: $context->parent->serviceId, needs: $context->serviceId);

        return $contextual;
    }

    /**
     * Attempt to resolve a registered definition.
     *
     * @param KernelContext $context The resolution context.
     *
     * @return mixed|null Resolved value when a definition exists, null otherwise.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-resolvedefinitionbinding
     */
    private function resolveDefinitionBinding(KernelContext $context) : mixed
    {
        $definition = $this->store->get(abstract: $context->serviceId);
        if ($definition === null) {
            return null;
        }

        return $definition->concrete;
    }

    /**
     * Attempt to resolve via autowiring.
     *
     * @param KernelContext $context The resolution context.
     *
     * @return mixed|null Resolved instance when class exists, null otherwise.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-resolveautowirecandidate
     */
    private function resolveAutowireCandidate(KernelContext $context, mixed &$current) : mixed
    {
        if ($current !== null) {
            return $current;
        }

        if (! class_exists(class: $context->serviceId)) {
            return null;
        }

        // Defer instantiation to the instantiate stage for trace visibility.
        return $context->serviceId;
    }

    /**
     * Transform a concrete definition (Closure, Object, Class-string) into an instance.
     *
     * @param KernelContext $context The current resolution context.
     *
     * @return mixed The evaluated result.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-evaluatecandidate
     */
    private function evaluateCandidate(mixed $candidate, KernelContext $context) : mixed
    {
        if ($candidate === null) {
            return null;
        }

        // 1. Literal Object: Return as-is
        if (is_object(value: $candidate) && ! ($candidate instanceof Closure)) {
            return $candidate;
        }

        // 2. Closure Factory: Execute with container and parameters
        if ($candidate instanceof Closure) {
            return ($candidate)($this->container, $context->overrides);
        }

        // 3. Class String: Register Instance or Delegate
        if (is_string(value: $candidate)) {
            // Check if we are delegating to another service ($concrete !== $id)
            if ($candidate !== $context->serviceId) {
                return $this->container->resolveContext(context: $context->child(serviceId: $candidate));
            }

            // Otherwise, defer instantiation to the instantiate stage
            return $candidate;
        }

        // 4. Literal Value (Strings/Ints/etc)
        return $candidate;
    }

    /**
     * Instantiate an evaluated candidate when needed.
     *
     * @param mixed         $candidate Candidate to build or return.
     * @param KernelContext $context   Resolution context for overrides and tracing.
     *
     * @return mixed Instantiated object or unchanged candidate.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-instantiatecandidate
     */
    private function instantiateCandidate(mixed $candidate, KernelContext $context) : mixed
    {
        if (is_string(value: $candidate)) {
            return $this->instantiator->build(class: $candidate, container: $this->container, overrides: $context->overrides, context: $context);
        }

        return $candidate;
    }

    /**
     * Notify an observer about the trace if provided.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-recordtrace
     */
    private function recordTrace(TraceObserverInterface|null $observer, ResolutionTrace $trace) : void
    {
        if ($observer === null) {
            return;
        }

        $observer->record(trace: $trace);
    }
}
