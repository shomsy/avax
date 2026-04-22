<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Compilation;

use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistration;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Container\DI\Capabilities\Declaration\Blueprints\CreateServiceBlueprint;
use Avax\Container\DI\Capabilities\Resolution\ResolvePlan;
use Closure;
use ReflectionException;
use ReflectionFunction;

/**
 * Describes and compiles service entries for the generated runtime artifact.
 */
final readonly class ServiceCompiler
{
    private MethodEmitter          $emitter;
    private CreateServiceBlueprint $blueprints;
    private ServiceRegistry        $registrations;

    public function __construct(
        ServiceRegistry        $registrations,
        CreateServiceBlueprint $blueprints,
        MethodEmitter          $emitter = new MethodEmitter
    )
    {
        $this->registrations = $registrations;
        $this->blueprints    = $blueprints;
        $this->emitter       = $emitter;
    }

    /**
     * @param string $serviceId
     *
     * @return array{serviceId: string, method: string, signature: string, source: string}
     * @throws ReflectionException
     */
    public function compile(string $serviceId) : array
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
     *
     * @return array{serviceId: string, method: string, signature: string, source: string}
     */
    public function compileFromDescription(array $description) : array
    {
        $source = $description['direct']
            ? $this->emitter->emitDirectMethod(
                methodName           : $description['method'],
                serviceId            : $description['serviceId'],
                class                : (string) $description['class'],
                plan                 : $description['plan'],
                registrationArguments: $description['registrationArguments'],
                needsFinish          : $description['needsFinish']
            )
            : $this->emitter->emitDynamicMethod(methodName: $description['method']);

        return [
            'serviceId' => $description['serviceId'],
            'method'    => $description['method'],
            'signature' => $description['signature'],
            'source'    => $source,
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
     * @throws ReflectionException
     */
    public function describe(string $serviceId) : array
    {
        $methodName            = $this->emitter->methodNameFor(serviceId: $serviceId);
        $registration          = $this->registrations->get(abstract: $serviceId);
        $candidate             = $this->candidateFor(serviceId: $serviceId, registration: $registration);
        $registrationArguments = $registration?->arguments ?? [];

        if (is_string(value: $candidate) && class_exists(class: $candidate) && $this->supportsCompiledArguments(arguments: $registrationArguments)) {
            $blueprint   = $this->blueprints->createFor(class: $candidate);
            $needsFinish = $blueprint->injectableProperties !== []
                || $blueprint->injectableMethods !== []
                || $this->registrations->hasExtenders(abstract: $serviceId);

            return [
                'serviceId'             => $serviceId,
                'method'                => $methodName,
                'signature'             => sha1(string: serialize(value: [
                                                              'serviceId' => $serviceId,
                                                              'candidate' => $candidate,
                                                              'lifetime'  => $registration?->lifetime,
                                                              'deferred'  => $registration?->deferred ?? false,
                                                              'arguments' => $registrationArguments,
                                                              'blueprint' => $blueprint->fingerprint,
                                                              'finish'    => $needsFinish,
                                                          ])),
                'direct'                => true,
                'class'                 => $candidate,
                'plan'                  => $blueprint->constructor,
                'registrationArguments' => $registrationArguments,
                'needsFinish'           => $needsFinish,
            ];
        }

        return [
            'serviceId'             => $serviceId,
            'method'                => $methodName,
            'signature'             => sha1(string: serialize(value: [
                                                          'serviceId' => $serviceId,
                                                          'candidate' => $this->dynamicSignature(candidate: $candidate),
                                                          'lifetime'  => $registration?->lifetime,
                                                          'deferred'  => $registration?->deferred ?? false,
                                                          'arguments' => $registrationArguments,
                                                      ])),
            'direct'                => false,
            'class'                 => null,
            'plan'                  => null,
            'registrationArguments' => $registrationArguments,
            'needsFinish'           => false,
        ];
    }

    private function candidateFor(string $serviceId, ServiceRegistration|null $registration) : mixed
    {
        if ($registration !== null) {
            return $registration->concrete;
        }

        return class_exists(class: $serviceId) ? $serviceId : null;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function supportsCompiledArguments(array $arguments) : bool
    {
        foreach ($arguments as $value) {
            if (! $this->supportsCompiledValue(value: $value)) {
                return false;
            }
        }

        return true;
    }

    private function supportsCompiledValue(mixed $value) : bool
    {
        if ($value === null || is_scalar(value: $value)) {
            return true;
        }

        if (! is_array(value: $value)) {
            return false;
        }

        foreach ($value as $item) {
            if (! $this->supportsCompiledValue(value: $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ReflectionException
     */
    private function dynamicSignature(mixed $candidate) : string
    {
        if ($candidate instanceof Closure) {
            $reflection = new ReflectionFunction(function: $candidate);

            return 'closure:' . ($reflection->getFileName() ?: 'internal')
                . ':' . $reflection->getStartLine()
                . ':' . $reflection->getEndLine();
        }

        if (is_object(value: $candidate)) {
            return 'object:' . $candidate::class;
        }

        return get_debug_type(value: $candidate) . ':' . var_export(value: $candidate, return: true);
    }
}
