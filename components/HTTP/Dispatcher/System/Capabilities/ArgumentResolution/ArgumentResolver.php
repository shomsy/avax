<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * ArgumentResolver - Resolves arguments for a controller method using reflection, DI, and Request parameters.
 */
final readonly class ArgumentResolver
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function resolve(ReflectionMethod $reflection, ServerRequestInterface $request) : array
    {
        $arguments = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            // 1. Resolve typed objects (ServerRequestInterface, DTOs, Services)
            if ($paramType instanceof ReflectionNamedType && ! $paramType->isBuiltin()) {
                $typeName = $paramType->getName();

                if (is_a($typeName, ServerRequestInterface::class, true)) {
                    $arguments[] = $request;

                    continue;
                }

                // If it's a DTO (FormRequest), we should have a DTO factory.
                // Assuming DTOs can be resolved from container or instantiated here.
                // Since RequestDtoFactory is currently unresolved in the new architecture,
                // we'll rely on the DI container for complex resolution.
                if ($this->container->has($typeName)) {
                    $arguments[] = $this->container->get($typeName);

                    continue;
                }
            }

            // 2. Resolve from Request Attributes (e.g., path parameters from Router)
            $attributeValue = $request->getAttribute($paramName);
            if ($attributeValue !== null) {
                $arguments[] = $attributeValue;

                continue;
            }

            // 3. Fallback to default value
            if ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();

                continue;
            }

            throw new RuntimeException(
                sprintf(
                    'Unable to resolve parameter "%s" for method "%s" in "%s"',
                    $paramName,
                    $reflection->getName(),
                    $reflection->getDeclaringClass()->getName(),
                ),
            );
        }

        return $arguments;
    }
}
