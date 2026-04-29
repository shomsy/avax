<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Resolution;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes\RuntimeInput;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;

/**
 * Compiles and resolves parameter plans for constructors, methods, and calls.
 */
final class ResolveDependencies
{
    /**
     * @param array                $parameters
     * @param array<string, mixed> $overrides
     * @param ServiceResolver      $resolver
     * @param ResolveRequest|null  $request
     *
     * @return array<int, mixed>
     * @throws Throwable
     */
    public function resolveParameters(
        array               $parameters,
        array               $overrides,
        ServiceResolver     $resolver,
        ResolveRequest|null $request = null
    ) : array
    {
        return $this->resolvePlan(
            plan     : $this->createPlan(parameters: $parameters),
            overrides: $overrides,
            resolver : $resolver,
            request  : $request
        );
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<int, mixed>
     * @throws ContainerException
     * @throws Throwable
     */
    public function resolvePlan(
        ResolvePlan         $plan,
        array               $overrides,
        ServiceResolver     $resolver,
        ResolveRequest|null $request
    ) : array
    {
        $resolved = [];

        foreach ($plan->parameters as $parameter) {
            $resolved[] = $this->resolveCompiledParameter(
                parameter: $parameter,
                overrides: $overrides,
                resolver : $resolver,
                request  : $request
            );
        }

        return $resolved;
    }

    /**
     * @param array{name: string, serviceId: string|null, source: string, inputName: string, hasDefault: bool, default:
     *                            string, allowsNull: bool} $parameter
     * @param array<string, mixed>                          $overrides
     * @param ServiceResolver                               $resolver
     * @param ResolveRequest|null                           $request
     *
     * @return mixed
     * @throws Throwable
     */
    private function resolveCompiledParameter(
        array               $parameter,
        array               $overrides,
        ServiceResolver     $resolver,
        ResolveRequest|null $request
    ) : mixed
    {
        if (array_key_exists(key: $parameter['name'], array: $overrides)) {
            return $overrides[$parameter['name']];
        }

        if (
            $request !== null
            && $parameter['serviceId'] === null
            && array_key_exists(key: $parameter['inputName'], array: $request->context)
        ) {
            return $request->context[$parameter['inputName']];
        }

        if ($parameter['serviceId'] !== null) {
            return $resolver->resolveRequest(
                request: $request?->child(serviceId: $parameter['serviceId'])
                             ?? new ResolveRequest(serviceId: $parameter['serviceId'])
            );
        }

        if ($parameter['hasDefault']) {
            return unserialize(data: base64_decode(string: $parameter['default']), options: ['allowed_classes' => false]);
        }

        if ($parameter['allowsNull']) {
            return null;
        }

        throw new ContainerException(
            message: $parameter['source'] === 'runtime'
                         ? "Runtime input [\${$parameter['inputName']}] is missing for [{$request?->serviceId}]. "
                       . "Dependency path [{$request?->getPath()}]. Likely fix: pass an explicit override, use forContext(), or add a default value."
                         : "Cannot resolve parameter [\${$parameter['name']}] for service [{$request?->serviceId}]. "
                       . "Dependency path [{$request?->getPath()}]. Likely fix: register the dependency, add an Inject attribute, or provide an override."
        );
    }

    /**
     * @param list<ReflectionParameter> $parameters
     */
    public function createPlan(array $parameters) : ResolvePlan
    {
        $compiled = [];

        foreach ($parameters as $parameter) {
            $compiled[] = [
                'name'       => $parameter->getName(),
                'serviceId'  => $this->serviceIdFor(parameter: $parameter),
                'source'     => $this->sourceFor(parameter: $parameter),
                'inputName'  => $this->inputNameFor(parameter: $parameter),
                'hasDefault' => $parameter->isDefaultValueAvailable(),
                'default'    => $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                        |> serialize(...)
                        |> base64_encode(...)
                    : '',
                'allowsNull' => $parameter->allowsNull(),
            ];
        }

        return new ResolvePlan(parameters: $compiled);
    }

    /**
     * Infers one service id from the parameter attribute or object type.
     */
    private function serviceIdFor(ReflectionParameter $parameter) : string|null
    {
        if ($parameter->getAttributes(name: RuntimeInput::class) !== []) {
            return null;
        }

        $attributes = $parameter->getAttributes(name: Inject::class);
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string(value: $inject->abstract) && $inject->abstract !== '') {
                return $inject->abstract;
            }
        }

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType instanceof ReflectionNamedType && ! $namedType->isBuiltin()) {
                    return $namedType->getName();
                }
            }
        }

        return null;
    }

    private function sourceFor(ReflectionParameter $parameter) : string
    {
        return $this->serviceIdFor(parameter: $parameter) !== null ? 'service' : 'runtime';
    }

    private function inputNameFor(ReflectionParameter $parameter) : string
    {
        $attributes = $parameter->getAttributes(name: RuntimeInput::class);
        if ($attributes === []) {
            return $parameter->getName();
        }

        $input = $attributes[0]->newInstance();
        if (is_string(value: $input->name) && trim(string: $input->name) !== '') {
            return trim(string: $input->name);
        }

        return $parameter->getName();
    }
}
