<?php

declare(strict_types=1);

namespace Avax\Container\Compilation;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Closure;
use ReflectionFunction;

final readonly class ServiceCompiler
{
    public function __construct(
        private ServiceRegistry $registrations,
        private CreateServiceBlueprint $blueprints,
        private MethodEmitter $emitter = new MethodEmitter
    ) {}

    /**
     * @return array{
     *   serviceId: string,
     *   method: string,
     *   signature: string,
     *   direct: bool,
     *   class: string|null,
     *   plan: \Avax\Container\DependencyInjection\Dependencies\Resolution\ResolvePlan|null,
     *   registrationArguments: array<string, mixed>,
     *   needsFinish: bool
     * }
     */
    public function describe(string $serviceId) : array
    {
        $methodName = $this->emitter->methodNameFor(serviceId: $serviceId);
        $registration = $this->registrations->get(abstract: $serviceId);
        $candidate = $this->candidateFor(serviceId: $serviceId, registration: $registration);

        if (is_string($candidate) && class_exists($candidate)) {
            $blueprint = $this->blueprints->createFor(class: $candidate);
            $needsFinish = $blueprint->injectableProperties !== []
                || $blueprint->injectableMethods !== []
                || $this->registrations->hasExtenders(abstract: $serviceId);

            return [
                'serviceId' => $serviceId,
                'method' => $methodName,
                'signature' => sha1(serialize([
                    'serviceId' => $serviceId,
                    'candidate' => $candidate,
                    'lifetime' => $registration?->lifetime,
                    'deferred' => $registration?->deferred ?? false,
                    'arguments' => $registration?->arguments ?? [],
                    'blueprint' => $blueprint->fingerprint,
                    'finish' => $needsFinish,
                ])),
                'direct' => true,
                'class' => $candidate,
                'plan' => $blueprint->constructor,
                'registrationArguments' => $registration?->arguments ?? [],
                'needsFinish' => $needsFinish,
            ];
        }

        return [
            'serviceId' => $serviceId,
            'method' => $methodName,
            'signature' => sha1(serialize([
                'serviceId' => $serviceId,
                'candidate' => $this->dynamicSignature(candidate: $candidate),
                'lifetime' => $registration?->lifetime,
                'deferred' => $registration?->deferred ?? false,
                'arguments' => $registration?->arguments ?? [],
            ])),
            'direct' => false,
            'class' => null,
            'plan' => null,
            'registrationArguments' => $registration?->arguments ?? [],
            'needsFinish' => false,
        ];
    }

    /**
     * @param array{
     *   serviceId: string,
     *   method: string,
     *   signature: string,
     *   direct: bool,
     *   class: string|null,
     *   plan: \Avax\Container\DependencyInjection\Dependencies\Resolution\ResolvePlan|null,
     *   registrationArguments: array<string, mixed>,
     *   needsFinish: bool
     * } $description
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
            'method' => $description['method'],
            'signature' => $description['signature'],
            'source' => $source,
        ];
    }

    /**
     * @return array{serviceId: string, method: string, signature: string, source: string}
     */
    public function compile(string $serviceId) : array
    {
        return $this->compileFromDescription(description: $this->describe(serviceId: $serviceId));
    }

    private function candidateFor(string $serviceId, ServiceRegistration|null $registration) : mixed
    {
        if ($registration !== null) {
            return $registration->concrete;
        }

        return class_exists($serviceId) ? $serviceId : null;
    }

    private function dynamicSignature(mixed $candidate) : string
    {
        if ($candidate instanceof Closure) {
            $reflection = new ReflectionFunction($candidate);

            return 'closure:' . ($reflection->getFileName() ?: 'internal')
                . ':' . $reflection->getStartLine()
                . ':' . $reflection->getEndLine();
        }

        if (is_object($candidate)) {
            return 'object:' . $candidate::class;
        }

        return get_debug_type($candidate) . ':' . var_export($candidate, true);
    }
}
