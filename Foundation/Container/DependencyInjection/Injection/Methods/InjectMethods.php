<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Injection\Methods;

use Avax\Container\DependencyInjection\Injection\Invocation\ResolveCallArguments;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

final readonly class InjectMethods
{
    public function __construct(
        private ResolveCallArguments $arguments
    ) {}

    /**
     * @param array<string, mixed> $overrides
     */
    public function inject(
        object $target,
        ServiceBlueprint $blueprint,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest $request
    ) : void {
        foreach ($blueprint->injectableMethods as $method) {
            $method->setAccessible(true);
            $arguments = $this->arguments->resolve(
                parameters: $method->getParameters(),
                overrides : $overrides,
                resolver  : $resolver,
                request   : $request
            );

            $method->invokeArgs($target, $arguments);
        }
    }
}
