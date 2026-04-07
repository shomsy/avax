<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Injection\Parameters;

use Avax\Container\DependencyInjection\Capability\Prototypes\Model\MethodPrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;

/**
 * Resolves argument lists for post-instantiation method injection.
 */
final readonly class ResolveMethodParameters
{
    public function __construct(
        private DependencyResolver $resolver
    ) {}

    /**
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     */
    public function resolve(
        MethodPrototype $method,
        array $overrides,
        ContainerInterface $container,
        KernelContext $context
    ) : array {
        return $this->resolver->resolveParameters(
            parameters: $method->parameters,
            overrides : $overrides,
            container : $container,
            context   : $context
        );
    }
}
