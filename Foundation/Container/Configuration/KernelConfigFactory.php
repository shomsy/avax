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
