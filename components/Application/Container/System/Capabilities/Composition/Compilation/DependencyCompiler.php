<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Compilation;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;
use Closure;
use ReflectionException;
use ReflectionFunction;

/**
 * Describes and compiles service entries for the generated runtime artifact.
 */
final readonly class DependencyCompiler
{
    public function __construct(private DependencyRegistry $dependencyRegistry, private CreateDependencyBlueprint $createDependencyBlueprint, private MethodEmitter $methodEmitter = new MethodEmitter())
    {
    }

    /**
     * @return array{serviceId: string, method: string, signature: string, source: string}
     *
     * @throws ReflectionException
     */
    public function compile(string $serviceId): array
    {
        return $this->compileFromDescription(description: $this->describe(serviceId: $serviceId));
    }

    /**
     * @param array{
     *   serviceId: string,
     *   method: string,
     *   signature: string,
     *   direct: bool,
     *   class: string|null,
     *   plan: ResolvePlan|null,
     *   registrationArguments: array<string, mixed>,
     *   needsFinish: bool
     * } $description
     * @return array{serviceId: string, method: string, signature: string, source: string}
     */
    public function compileFromDescription(array $description): array
    {
        $source = $description['direct']
            ? $this->methodEmitter->emitDirectMethod(
                methodName           : $description['method'],
                serviceId            : $description['serviceId'],
                class                : (string) $description['class'],
                registrationArguments: $description['registrationArguments'],
                needsFinish          : $description['needsFinish'],
                plan                 : $description['plan'],
            )
            : $this->methodEmitter->emitDynamicMethod(methodName: $description['method']);

        return [
            'serviceId' => $description['serviceId'],
            'method' => $description['method'],
            'signature' => $description['signature'],
            'source' => $source,
        ];
    }

    /**
     * @return array{
     *   serviceId: string,
     *   method: string,
     *   signature: string,
     *   direct: bool,
     *   class: string|null,
     *   plan: ResolvePlan|null,
     *   registrationArguments: array<string, mixed>,
     *   needsFinish: bool
     * }
     *
     * @throws ReflectionException
     */
    public function describe(string $serviceId): array
    {
        $methodName = $this->methodEmitter->methodNameFor(serviceId: $serviceId);
        $registration = $this->dependencyRegistry->get(abstract: $serviceId);
        $candidate = $this->candidateFor(serviceId: $serviceId, registration: $registration);
        $registrationArguments = $registration?->arguments ?? [];

        if (is_string(value: $candidate) && class_exists(class: $candidate) && $this->supportsCompiledArguments(arguments: $registrationArguments)) {
            $blueprint = $this->createDependencyBlueprint->createFor(class: $candidate);
            $needsFinish = $blueprint->injectableProperties !== []
                || $blueprint->injectableMethods !== []
                || $this->dependencyRegistry->hasExtenders(abstract: $serviceId);

            return [
                'serviceId' => $serviceId,
                'method' => $methodName,
                'signature' => sha1(string: serialize(value: [
                    'serviceId' => $serviceId,
                    'candidate' => $candidate,
                    'lifetime' => $registration?->lifetime,
                    'deferred' => $registration?->deferred ?? false,
                    'arguments' => $registrationArguments,
                    'blueprint' => $blueprint->fingerprint,
                    'finish' => $needsFinish,
                ])),
                'direct' => true,
                'class' => $candidate,
                'plan' => $blueprint->constructor,
                'registrationArguments' => $registrationArguments,
                'needsFinish' => $needsFinish,
            ];
        }

        return [
            'serviceId' => $serviceId,
            'method' => $methodName,
            'signature' => sha1(string: serialize(value: [
                'serviceId' => $serviceId,
                'candidate' => $this->dynamicSignature(candidate: $candidate),
                'lifetime' => $registration?->lifetime,
                'deferred' => $registration?->deferred ?? false,
                'arguments' => $registrationArguments,
            ])),
            'direct' => false,
            'class' => null,
            'plan' => null,
            'registrationArguments' => $registrationArguments,
            'needsFinish' => false,
        ];
    }

    private function candidateFor(string $serviceId, ?DependencyRegistration $dependencyRegistration): mixed
    {
        if ($dependencyRegistration instanceof DependencyRegistration) {
            return $dependencyRegistration->concrete;
        }

        return class_exists(class: $serviceId) ? $serviceId : null;
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function supportsCompiledArguments(array $arguments): bool
    {
        return array_all($arguments, fn ($value): bool => $this->supportsCompiledValue(value: $value));
    }

    private function supportsCompiledValue(mixed $value): bool
    {
        if ($value === null || is_scalar(value: $value)) {
            return true;
        }

        if (! is_array(value: $value)) {
            return false;
        }

        return array_all($value, fn ($item): bool => $this->supportsCompiledValue(value: $item));
    }

    /**
     * @throws ReflectionException
     */
    private function dynamicSignature(mixed $candidate): string
    {
        if ($candidate instanceof Closure) {
            $reflectionFunction = new ReflectionFunction(function: $candidate);

            return 'closure:'.($reflectionFunction->getFileName() ?: 'internal')
                .':'.$reflectionFunction->getStartLine()
                .':'.$reflectionFunction->getEndLine();
        }

        if (is_object(value: $candidate)) {
            return 'object:'.$candidate::class;
        }

        return get_debug_type(value: $candidate).':'.var_export(value: $candidate, return: true);
    }
}
