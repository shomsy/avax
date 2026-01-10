<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Actions\Inject\InjectDependencies;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Operate\Shutdown\TerminateContainer;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Guard\Rules\ContainerPolicy;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Avax\Container\Observe\Timeline\ResolutionTimeline;

/**
 * Kernel Configuration - Centralized configuration for ContainerKernel
 *
 * Immutable configuration object containing all collaborators and settings
 * needed by ContainerKernel. Provides fluent API for configuration building, ensuring type-safe and predictable kernel setup.
 *
 * @see docs_md/Core/Kernel/KernelConfig.md#quick-summary
 */
final readonly class KernelConfig
{
    /**
     * Initialize kernel configuration with all collaborators.
     *
     * @param EngineInterface         $engine           Resolution engine
     * @param InjectDependencies      $injector         Dependency injection system
     * @param InvokeAction            $invoker          Callable invocation system
     * @param ScopeManager            $scopes           Instance lifetime management
     * @param ServicePrototypeFactory $prototypeFactory Service analysis factory
     * @param ResolutionTimeline      $timeline         Resolution tracking
     * @param CollectMetrics|null     $metrics          Optional metrics collection
     * @param ContainerPolicy|null    $policy           Optional security policy
     * @param TerminateContainer|null $terminator       Optional shutdown handler
     * @param bool                    $autoDefine       Enable automatic service definition
     * @param bool                    $strictMode       Enable strict validation mode
     * @param bool                    $devMode          Enable development mode features
     * @see docs_md/Core/Kernel/KernelConfig.md#method-__construct
     */
    public function __construct(
        public EngineInterface         $engine,
        public InjectDependencies      $injector,
        public InvokeAction            $invoker,
        public ScopeManager            $scopes,
        public ServicePrototypeFactory $prototypeFactory,
        public ResolutionTimeline      $timeline,
        public CollectMetrics|null     $metrics = null,
        public ContainerPolicy|null    $policy = null,
        public TerminateContainer|null $terminator = null,
        public bool                    $autoDefine = false,
        public bool                    $strictMode = false,
        public bool                    $devMode = true
    ) {}

    /**
     * Create configuration with required collaborators only.
     *
     * @param EngineInterface         $engine           Resolution engine
     * @param InjectDependencies      $injector         Dependency injection system
     * @param InvokeAction            $invoker          Callable invocation system
     * @param ScopeManager            $scopes           Instance lifetime management
     * @param ServicePrototypeFactory $prototypeFactory Service analysis factory
     * @param ResolutionTimeline      $timeline         Resolution tracking
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-create
     */
    public static function create(
        EngineInterface         $engine,
        InjectDependencies      $injector,
        InvokeAction            $invoker,
        ScopeManager            $scopes,
        ServicePrototypeFactory $prototypeFactory,
        ResolutionTimeline      $timeline
    ): self {
        return new self(
            engine: $engine,
            injector: $injector,
            invoker: $invoker,
            scopes: $scopes,
            prototypeFactory: $prototypeFactory,
            timeline: $timeline
        );
    }

    /**
     * Enable or disable strict validation mode.
     *
     * @param bool $strict Enable strict mode if true
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withstrictmode
     */
    public function withStrictMode(bool $strict = true): self
    {
        return $this->cloneWith(strictMode: $strict);
    }

    /**
     * Internal helper to create a modified clone of the configuration.
     *
     * @param CollectMetrics|null     $metrics
     * @param ContainerPolicy|null    $policy
     * @param TerminateContainer|null $terminator
     * @param bool|null               $autoDefine
     * @param bool|null               $strictMode
     * @param bool|null               $devMode
     * @return self
     */
    private function cloneWith(
        CollectMetrics|null     $metrics = null,
        ContainerPolicy|null    $policy = null,
        TerminateContainer|null $terminator = null,
        bool|null               $autoDefine = null,
        bool|null               $strictMode = null,
        bool|null               $devMode = null
    ): self {
        return new self(
            engine: $this->engine,
            injector: $this->injector,
            invoker: $this->invoker,
            scopes: $this->scopes,
            prototypeFactory: $this->prototypeFactory,
            timeline: $this->timeline,
            metrics: $metrics ?? $this->metrics,
            policy: $policy ?? $this->policy,
            terminator: $terminator ?? $this->terminator,
            autoDefine: $autoDefine ?? $this->autoDefine,
            strictMode: $strictMode ?? $this->strictMode,
            devMode: $devMode ?? $this->devMode
        );
    }

    /**
     * Enable or disable automatic service definition.
     *
     * @param bool $autoDefine Enable auto-definition if true
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withautodefine
     */
    public function withAutoDefine(bool $autoDefine = true): self
    {
        return $this->cloneWith(autoDefine: $autoDefine);
    }

    /**
     * Configure metrics collection.
     *
     * @param CollectMetrics $metrics Metrics collector
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withmetrics
     */
    public function withMetrics(CollectMetrics $metrics): self
    {
        return $this->cloneWith(metrics: $metrics);
    }

    /**
     * Configure security policy.
     *
     * @param ContainerPolicy $policy Security policy
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withpolicy
     */
    public function withPolicy(ContainerPolicy $policy): self
    {
        return $this->cloneWith(policy: $policy);
    }

    /**
     * Configure shutdown terminator.
     *
     * @param TerminateContainer $terminator Shutdown handler
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withterminator
     */
    public function withTerminator(TerminateContainer $terminator): self
    {
        return $this->cloneWith(terminator: $terminator);
    }

    /**
     * Enable or disable development mode.
     *
     * @param bool $devMode Enable dev mode if true
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withdevmode
     */
    public function withDevMode(bool $devMode): self
    {
        return $this->cloneWith(devMode: $devMode);
    }
}
