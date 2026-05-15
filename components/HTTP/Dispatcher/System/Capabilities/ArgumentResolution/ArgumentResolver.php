<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution;

use Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;
use Avax\Components\HTTP\SecureRequest\System\PublicSurface\SecureRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * ArgumentResolver - Resolves arguments for a controller method using reflection, DI, and Request parameters.
 *
 * SecureRequest handling:
 * - Detects SecureRequest subclasses via is_a() check
 * - Builds input from HTTP request (body, query, route params)
 * - Hydrates and validates through DataTransfer via SecureRequest lifecycle
 * - Throws SecureRequestValidationFailed / SecureRequestAuthorizationFailed on failure
 */
final readonly class ArgumentResolver
{
    public function __construct(
        private ContainerInterface $container,
        private SecureRequestInputBuilder $inputBuilder,
    ) {}

    public function resolve(ReflectionMethod $reflectionMethod, ServerRequestInterface $serverRequest) : array
    {
        $arguments = [];

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $paramName = $reflectionParameter->getName();
            $paramType = $reflectionParameter->getType();

            // 1. Resolve typed objects (ServerRequestInterface, SecureRequest, DTOs, Services)
            if ($paramType instanceof ReflectionNamedType && ! $paramType->isBuiltin()) {
                $typeName = $paramType->getName();

                if (is_a($typeName, ServerRequestInterface::class, true)) {
                    $arguments[] = $serverRequest;

                    continue;
                }

                // SecureRequest resolution via Container + DataTransfer lifecycle
                if (is_a($typeName, SecureRequest::class, true)) {
                    $instance    = $this->resolveSecureRequest($typeName, $serverRequest);
                    $arguments[] = $instance;

                    continue;
                }

                if ($this->container->has($typeName)) {
                    $arguments[] = $this->container->get($typeName);

                    continue;
                }
            }

            // 2. Resolve from Request Attributes (e.g., path parameters from Router)
            $attributeValue = $serverRequest->getAttribute($paramName);
            if ($attributeValue !== null) {
                $arguments[] = $attributeValue;

                continue;
            }

            // 3. Fallback to default value
            if ($reflectionParameter->isDefaultValueAvailable()) {
                $arguments[] = $reflectionParameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException(
                sprintf(
                    'Unable to resolve parameter "%s" for method "%s" in "%s"',
                    $paramName,
                    $reflectionMethod->getName(),
                    $reflectionMethod->getDeclaringClass()->getName(),
                ),
            );
        }

        return $arguments;
    }

    /**
     * @param class-string<SecureRequest> $typeName
     */
    private function resolveSecureRequest(string $typeName, ServerRequestInterface $serverRequest) : SecureRequest
    {
        $input = $this->inputBuilder->buildInput(request: $serverRequest);

        $reflectionClass = new ReflectionClass($typeName);
        $instance        = $reflectionClass->newInstance();

        $instance->runLifecycle(input: $input);

        return $instance;
    }
}
