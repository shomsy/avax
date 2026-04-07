<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Injection\Methods;

use Avax\Container\DependencyInjection\Capability\Injection\Parameters\ResolveMethodParameters;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Invokes injectable methods after an object has been constructed.
 */
final readonly class MethodInjector
{
    public function __construct(
        private ResolveMethodParameters $parameterResolver
    ) {}

    /**
     * @param array<string, mixed> $overrides
     */
    public function inject(
        object $target,
        ServicePrototype $prototype,
        ReflectionClass $reflection,
        array $overrides,
        ContainerInterface $container,
        KernelContext $context
    ) : void {
        if ($prototype->injectedMethods === []) {
            return;
        }

        foreach ($prototype->injectedMethods as $methodPrototype) {
            $arguments = $this->parameterResolver->resolve(
                method    : $methodPrototype,
                overrides : $overrides,
                container : $container,
                context   : $context
            );

            $method = $reflection->getMethod(name: $methodPrototype->name);
            $method->invoke($target, ...$arguments);
        }
    }
}
