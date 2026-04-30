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
    private ResolveDependencies $dependencies;

    public function __construct(
        ResolveDependencies $dependencies,
    )
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<int, mixed>
     */
    public function resolve(
        array           $parameters,
        array           $overrides,
        ResolveDependency $resolver,
        ResolveRequest  $request = null,
    ) : array
    {
        return $this->resolvePlan(
            plan     : $this->createPlan(parameters: $parameters),
            overrides: $overrides,
            resolver : $resolver,
            request  : $request,
        );
    }

    /**
     * @param ResolvePlan         $plan
     * @param array<string, mixed> $overrides
     * @param ResolveDependency $resolver
     * @param ResolveRequest|null $request
     *
     * @return array<int, mixed>
     * @throws Throwable
     */
    public function resolvePlan(
        ResolvePlan     $plan,
        array           $overrides,
        ResolveDependency $resolver,
        ResolveRequest  $request = null,
    ) : array
    {
        return $this->dependencies->resolvePlan(
            plan     : $plan,
            overrides: $overrides,
            resolver : $resolver,
            request  : $request,
        );
    }

    /**
     * @param list<ReflectionParameter> $parameters
     */
    public function createPlan(array $parameters) : ResolvePlan
    {
        return $this->dependencies->createPlan(parameters: $parameters);
    }
}
