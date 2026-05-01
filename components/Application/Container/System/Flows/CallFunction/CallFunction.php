<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\CallFunction;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use ReflectionException;

/**
 * Public invocation flow for executing callables through the container.
 */
final readonly class CallFunction
{
    public function __construct(private ResolveDependency $resolveDependency) {}

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws ReflectionException
     */
    public function call(callable|string $target, array $parameters = []) : mixed
    {
        return $this->resolveDependency->call(callable: $target, parameters: $parameters);
    }
}
