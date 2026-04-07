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
     * @return array{serviceId: string, method: string, signature: string, source: string}
     */
    public function compile(string $serviceId) : array
    {
        $methodName = $this->emitter->methodNameFor(serviceId: $serviceId);
        $registration = $this->registrations->get(abstract: $serviceId);
        $candidate = $this->candidateFor(serviceId: $serviceId, registration: $registration);

        if (is_string($candidate) && class_exists($candidate)) {
            $blueprint = $this->blueprints->createFor(class: $candidate);

            return [
                'serviceId' => $serviceId,
                'method' => $methodName,
                'signature' => sha1(serialize([
                    'serviceId' => $serviceId,
                    'candidate' => $candidate,
                    'lifetime' => $registration?->lifetime,
                    'arguments' => $registration?->arguments ?? [],
                    'blueprint' => $blueprint->fingerprint,
                    'finish' => $blueprint->injectableProperties !== []
                        || $blueprint->injectableMethods !== []
                        || $this->registrations->hasExtenders(abstract: $serviceId),
                ])),
                'source' => $this->emitter->emitDirectMethod(
                    methodName : $methodName,
                    serviceId  : $serviceId,
                    class      : $candidate,
                    plan       : $blueprint->constructor,
                    registrationArguments: $registration?->arguments ?? [],
                    needsFinish: $blueprint->injectableProperties !== []
                        || $blueprint->injectableMethods !== []
                        || $this->registrations->hasExtenders(abstract: $serviceId)
                ),
            ];
        }

        return [
            'serviceId' => $serviceId,
            'method' => $methodName,
            'signature' => sha1(serialize([
                'serviceId' => $serviceId,
                'candidate' => $this->dynamicSignature(candidate: $candidate),
                'lifetime' => $registration?->lifetime,
                'arguments' => $registration?->arguments ?? [],
            ])),
            'source' => $this->emitter->emitDynamicMethod(methodName: $methodName),
        ];
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
