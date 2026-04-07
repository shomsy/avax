<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Injection\Invocation;

use Avax\Container\Errors\ContainerException;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Closure;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

final class FunctionCaller
{
    private ServiceResolver|null $resolver = null;

    public function __construct(
        private readonly ResolveCallArguments $arguments
    ) {}

    public function setResolver(ServiceResolver $resolver) : void
    {
        $this->resolver = $resolver;
    }

    public function call(
        callable|string $target,
        array $parameters = [],
        ResolveRequest|null $request = null
    ) : mixed {
        if ($this->resolver === null) {
            throw new ContainerException(message: 'FunctionCaller is not attached to a resolver.');
        }

        $normalized = $this->normalizeTarget(target: $target);
        $reflection = $this->reflect(target: $normalized);
        $arguments  = $this->arguments->resolve(
            parameters: $reflection->getParameters(),
            overrides : $parameters,
            resolver  : $this->resolver,
            request   : $request ?? new ResolveRequest(serviceId: $this->nameOf(reflection: $reflection))
        );

        if ($reflection instanceof ReflectionMethod) {
            $object = is_object($normalized)
                ? $normalized
                : (is_array($normalized) && is_object($normalized[0]) ? $normalized[0] : null);

            return $reflection->invokeArgs($object, $arguments);
        }

        /** @var ReflectionFunction $reflection */
        return $reflection->invokeArgs($arguments);
    }

    private function normalizeTarget(callable|string $target) : callable|string|array
    {
        if (is_string($target) && class_exists($target) && method_exists($target, '__invoke')) {
            return $this->resolver->get(id: $target);
        }

        if (is_string($target) && str_contains($target, '@')) {
            [$class, $method] = explode('@', $target, 2);

            return [$this->resolver->get(id: $class), $method];
        }

        if (is_string($target) && str_contains($target, '::')) {
            [$class, $method] = explode('::', $target, 2);
            $reflection = new ReflectionMethod($class, $method);

            return $reflection->isStatic()
                ? [$class, $method]
                : [$this->resolver->get(id: $class), $method];
        }

        if (is_array($target) && is_string($target[0]) && class_exists($target[0])) {
            $reflection = new ReflectionMethod($target[0], (string) $target[1]);
            if (! $reflection->isStatic()) {
                return [$this->resolver->get(id: $target[0]), $target[1]];
            }
        }

        return $target;
    }

    private function reflect(callable|string|array $target) : ReflectionFunctionAbstract
    {
        if (is_array($target)) {
            return new ReflectionMethod($target[0], (string) $target[1]);
        }

        if ($target instanceof Closure || is_string($target)) {
            return new ReflectionFunction($target);
        }

        if (is_object($target) && method_exists($target, '__invoke')) {
            return new ReflectionMethod($target, '__invoke');
        }

        throw new ContainerException(message: 'Unsupported callable target.');
    }

    private function nameOf(ReflectionFunctionAbstract $reflection) : string
    {
        if ($reflection instanceof ReflectionMethod) {
            return 'call:' . $reflection->class . '::' . $reflection->getName();
        }

        return 'call:' . $reflection->getName();
    }
}
