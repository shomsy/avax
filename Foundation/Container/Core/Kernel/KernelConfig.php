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
     * @param EngineInterface $engine Resolution engine
     * @param InjectDependencies $injector Dependency injection system
     * @param InvokeAction $invoker Callable invocation system
     * @param ScopeManager $scopes Instance lifetime management
     * @param ServicePrototypeFactory $prototypeFactory Service analysis factory
     * @param ResolutionTimeline $timeline Resolution tracking
     * @param CollectMetrics|null $metrics Optional metrics collection
     * @param ContainerPolicy|null $policy Optional security policy
     * @param TerminateContainer|null $terminator Optional shutdown handler
     * @param bool $autoDefine Enable automatic service definition
     * @param bool $strictMode Enable strict validation mode
     * @param bool $devMode Enable development mode features
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
     * Provides a factory method for creating KernelConfig instances with only the required collaborators,
     * allowing optional settings to be configured through fluent methods afterward.
     *
     * @param EngineInterface $engine Resolution engine
     * @param InjectDependencies $injector Dependency injection system
     * @param InvokeAction $invoker Callable invocation system
     * @param ScopeManager $scopes Instance lifetime management
     * @param ServicePrototypeFactory $prototypeFactory Service analysis factory
     * @param ResolutionTimeline $timeline Resolution tracking
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
    ) : self
    {
        return new self(
            engine          : $engine,
            injector        : $injector,
            invoker         : $invoker,
            scopes          : $scopes,
            prototypeFactory: $prototypeFactory,
            timeline        : $timeline
        );
    }

    /**
     * Enable or disable strict validation mode.
     *
     * Configures whether the kernel should perform additional validation checks,
     * providing stricter error detection at the cost of some performance.
     *
     * @param bool $strict Enable strict mode if true
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withStrictMode
     */
    public function withStrictMode(bool $strict = true) : self
    {
        return $this->cloneWith(strictMode: $strict);
    }

    private function cloneWith(
        CollectMetrics|null     $metrics = null,
        ContainerPolicy|null    $policy = null,
        TerminateContainer|null $terminator = null,
        bool|null               $autoDefine = null,
        bool|null               $strictMode = null,
        bool|null               $devMode = null
    ) : self
    {
        return new self(
            engine          : $this->engine,
            injector        : $this->injector,
            invoker         : $this->invoker,
            scopes          : $this->scopes,
            prototypeFactory: $this->prototypeFactory,
            timeline        : $this->timeline,
            metrics         : $metrics ?? $this->metrics,
            policy          : $policy ?? $this->policy,
            terminator      : $terminator ?? $this->terminator,
            autoDefine      : $autoDefine ?? $this->autoDefine,
            strictMode      : $strictMode ?? $this->strictMode,
            devMode         : $devMode ?? $this->devMode
        );
    }

    /**
     * Enable or disable automatic service definition.
     *
     * Configures whether the kernel should automatically register services based on class analysis,
     * reducing the need for explicit service registration.
     *
     * @param bool $autoDefine Enable auto-definition if true
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withAutoDefine
     */
    public function withAutoDefine(bool $autoDefine = true) : self
    {
        return $this->cloneWith(autoDefine: $autoDefine);
    }

    /**
     * Configure metrics collection.
     *
     * Sets up a metrics collector to track kernel performance and usage statistics.
     *
     * @param CollectMetrics $metrics Metrics collector
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withMetrics
     */
    public function withMetrics(CollectMetrics $metrics) : self
    {
        return $this->cloneWith(metrics: $metrics);
    }

    /**
     * Configure security policy.
     *
     * Establishes security rules and validation policies for container operations.
     *
     * @param ContainerPolicy $policy Security policy
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withPolicy
     */
    public function withPolicy(ContainerPolicy $policy) : self
    {
        return $this->cloneWith(policy: $policy);
    }

    /**
     * Configure shutdown terminator.
     *
     * Sets up a shutdown handler to manage proper cleanup when the container terminates.
     *
     * @param TerminateContainer $terminator Shutdown handler
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withTerminator
     */
    public function withTerminator(TerminateContainer $terminator) : self
    {
        return $this->cloneWith(terminator: $terminator);
    }

    /**
     * Enable or disable development mode.
     *
     * Configures development-friendly features like detailed error messages and debugging aids.
     *
     * @param bool $devMode Enable dev mode if true
     * @return self New configuration instance
     * @see docs_md/Core/Kernel/KernelConfig.md#method-withDevMode
     */
    public function withDevMode(bool $devMode) : self
    {
        return $this->cloneWith(devMode: $devMode);
    }
}
