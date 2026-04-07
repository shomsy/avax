<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Configuration;

use Avax\Container\DependencyInjection\Capability\Injection\InjectDependencies;
use Avax\Container\DependencyInjection\Capability\Invocation\InvokeAction;
use Avax\Container\DependencyInjection\Capability\Observability\Metrics\CollectMetrics;
use Avax\Container\DependencyInjection\Capability\Observability\Timeline\ResolutionTimeline;
use Avax\Container\DependencyInjection\Capability\Policies\ContainerPolicy;
use Avax\Container\DependencyInjection\Capability\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\EngineInterface;
use Avax\Container\DependencyInjection\Capability\Scopes\ScopeManager;

/**
 * Factory that normalizes default kernel flags.
 */
final class KernelConfigFactory
{
    public function create(
        EngineInterface                  $engine,
        InjectDependencies               $injector,
        InvokeAction                     $invoker,
        ScopeManager                     $scopes,
        ServicePrototypeFactoryInterface $prototypeFactory,
        ResolutionTimeline               $timeline,
        CollectMetrics|null              $metrics = null,
        ContainerPolicy|null             $policy = null,
        bool|null                        $debug = null,
        bool|null                        $strictMode = null,
        bool|null                        $autoDefine = null,
        bool|null                        $devMode = null
    ) : KernelConfig
    {
        $debug              ??= false;
        $resolvedStrictMode = $strictMode ?? $debug;
        $resolvedAutoDefine = $autoDefine ?? ! $resolvedStrictMode;
        $resolvedDevMode    = $devMode ?? $debug;

        return new KernelConfig(
            engine          : $engine,
            injector        : $injector,
            invoker         : $invoker,
            scopes          : $scopes,
            prototypeFactory: $prototypeFactory,
            timeline        : $timeline,
            metrics         : $metrics,
            policy          : $policy,
            autoDefine      : $resolvedAutoDefine,
            strictMode      : $resolvedStrictMode,
            devMode         : $resolvedDevMode
        );
    }
}
