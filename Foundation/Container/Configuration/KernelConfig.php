<?php

declare(strict_types=1);

namespace Avax\Container\Configuration;

use Avax\Container\Capabilities\Injection\InjectDependencies;
use Avax\Container\Capabilities\Invocation\InvokeAction;
use Avax\Container\Capabilities\Observability\Metrics\CollectMetrics;
use Avax\Container\Capabilities\Observability\Timeline\ResolutionTimeline;
use Avax\Container\Capabilities\Policies\ContainerPolicy;
use Avax\Container\Capabilities\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\Capabilities\Resolution\Engine\EngineInterface;
use Avax\Container\Capabilities\Scopes\ScopeManager;

/**
 * Immutable runtime assembly for the kernel and pipeline.
 */
final readonly class KernelConfig
{
    public function __construct(
        public EngineInterface                  $engine,
        public InjectDependencies               $injector,
        public InvokeAction                     $invoker,
        public ScopeManager                     $scopes,
        public ServicePrototypeFactoryInterface $prototypeFactory,
        public ResolutionTimeline               $timeline,
        public CollectMetrics|null              $metrics = null,
        public ContainerPolicy|null             $policy = null,
        public bool                             $autoDefine = false,
        public bool                             $strictMode = false,
        public bool                             $devMode = true
    ) {}

    public static function create(
        EngineInterface                  $engine,
        InjectDependencies               $injector,
        InvokeAction                     $invoker,
        ScopeManager                     $scopes,
        ServicePrototypeFactoryInterface $prototypeFactory,
        ResolutionTimeline               $timeline
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

    public function withStrictMode(bool $strict = true) : self
    {
        return $this->cloneWith(strictMode: $strict);
    }

    public function withAutoDefine(bool $autoDefine = true) : self
    {
        return $this->cloneWith(autoDefine: $autoDefine);
    }

    public function withMetrics(CollectMetrics $metrics) : self
    {
        return $this->cloneWith(metrics: $metrics);
    }

    public function withPolicy(ContainerPolicy $policy) : self
    {
        return $this->cloneWith(policy: $policy);
    }

    public function withDevMode(bool $devMode) : self
    {
        return $this->cloneWith(devMode: $devMode);
    }

    private function cloneWith(
        CollectMetrics|null  $metrics = null,
        ContainerPolicy|null $policy = null,
        bool|null            $autoDefine = null,
        bool|null            $strictMode = null,
        bool|null            $devMode = null
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
            autoDefine      : $autoDefine ?? $this->autoDefine,
            strictMode      : $strictMode ?? $this->strictMode,
            devMode         : $devMode ?? $this->devMode
        );
    }
}
