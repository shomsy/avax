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
 * Factory that builds KernelConfig with consistent defaults and optional overrides for strict/dev/autodefine profiles.
 *
 * @see docs/Core/Kernel/KernelConfigFactory.md#quick-summary
 */
final class KernelConfigFactory
{
    /**
     * Create a configured KernelConfig instance.
     *
     * @param bool|null $strictMode Override strictness (defaults to $debug)
     * @param bool|null $autoDefine Override auto-definitions (defaults to inverse of strictness)
     * @param bool|null $devMode    Override dev features (defaults to $debug)
     *
     * @see docs/Core/Kernel/KernelConfigFactory.md#method-create
     */
    public function create(
        EngineInterface         $engine,
        InjectDependencies      $injector,
        InvokeAction            $invoker,
        ScopeManager            $scopes,
        ServicePrototypeFactory $prototypeFactory,
        ResolutionTimeline      $timeline,
        CollectMetrics|null     $metrics = null,
        ContainerPolicy|null    $policy = null,
        TerminateContainer|null $terminator = null,
        bool|null               $debug = null,
        bool|null               $strictMode = null,
        bool|null               $autoDefine = null,
        bool|null               $devMode = null
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
            terminator      : $terminator,
            autoDefine      : $resolvedAutoDefine,
            strictMode      : $resolvedStrictMode,
            devMode         : $resolvedDevMode
        );
    }
}
