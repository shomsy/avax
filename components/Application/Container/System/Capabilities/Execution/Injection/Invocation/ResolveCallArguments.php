<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use ReflectionParameter;
use Throwable;

final readonly class ResolveCallArguments
{
    public function __construct(private ResolveDependencies $resolveDependencies)
    {
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<int, mixed>
     */
    public function resolve(
        array $parameters,
        array $overrides,
        ResolveDependency $resolveDependency,
        ?ResolveRequest $resolveRequest = null,
    ): array {
        return $this->resolvePlan(
            overrides: $overrides,
            plan     : $this->createPlan(parameters: $parameters),
            resolver : $resolveDependency,
            request  : $resolveRequest,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<int, mixed>
     *
     * @throws Throwable
     */
    public function resolvePlan(
        ResolvePlan $resolvePlan,
        array $overrides,
        ResolveDependency $resolveDependency,
        ?ResolveRequest $resolveRequest = null,
    ): array {
        return $this->resolveDependencies->resolvePlan(
            overrides: $overrides,
            plan     : $resolvePlan,
            resolver : $resolveDependency,
            request  : $resolveRequest,
        );
    }

    /**
     * @param  list<ReflectionParameter>  $parameters
     */
    public function createPlan(array $parameters): ResolvePlan
    {
        return $this->resolveDependencies->createPlan(parameters: $parameters);
    }
}
