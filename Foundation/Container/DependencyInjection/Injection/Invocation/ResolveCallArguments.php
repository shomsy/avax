<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Injection\Invocation;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveDependencies;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use ReflectionParameter;

final readonly class ResolveCallArguments
{
    public function __construct(
        private ResolveDependencies $dependencies
    ) {}

    /**
     * @param list<ReflectionParameter> $parameters
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     */
    public function resolve(
        array $parameters,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest|null $request = null
    ) : array {
        return $this->dependencies->resolveParameters(
            parameters: $parameters,
            overrides : $overrides,
            resolver  : $resolver,
            request   : $request
        );
    }
}
