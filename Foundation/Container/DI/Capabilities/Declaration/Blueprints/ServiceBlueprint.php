<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Blueprints;

use Avax\Container\DI\Capabilities\Resolution\ResolvePlan;

final readonly class ServiceBlueprint
{
    /**
     * @param list<array{name: string, serviceId: string|null, readonly: bool}> $injectableProperties
     * @param list<array{name: string, plan: ResolvePlan}>                      $injectableMethods
     */
    public function __construct(
        public string           $class,
        public bool             $instantiable = false,
        public ResolvePlan|null $constructor = null,
        public array            $injectableProperties = [],
        public array            $injectableMethods = [],
        public bool             $shared = false,
        public string           $fingerprint = ''
    ) {}

    public static function __set_state(array $state) : self
    {
        return new self(
            class               : $state['class'],
            instantiable        : $state['instantiable'] ?? false,
            constructor         : $state['constructor'] ?? null,
            injectableProperties: $state['injectableProperties'] ?? [],
            injectableMethods   : $state['injectableMethods'] ?? [],
            shared              : $state['shared'] ?? false,
            fingerprint         : $state['fingerprint'] ?? ''
        );
    }
}
