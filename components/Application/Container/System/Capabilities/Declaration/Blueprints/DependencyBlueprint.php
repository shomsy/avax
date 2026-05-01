<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;

final readonly class DependencyBlueprint
{
    public bool $shared;

    public array $injectableMethods;

    public array $injectableProperties;

    public bool $instantiable;

    /**
     * @param list<array{name: string, serviceId: string|null, readonly: bool}> $injectableProperties
     * @param list<array{name: string, plan: ResolvePlan}> $injectableMethods
     */
    public function __construct(
        public string $class,
        bool          $instantiable = null,
        public ResolvePlan|null $resolvePlan = null,
        array         $injectableProperties = null,
        array         $injectableMethods = null,
        bool          $shared = null,
        public string $fingerprint = '',
    )
    {
        $instantiable         ??= false;
        $injectableProperties ??= [];
        $injectableMethods    ??= [];
        $shared               ??= false;
        $this->instantiable         = $instantiable;
        $this->injectableProperties = $injectableProperties;
        $this->injectableMethods    = $injectableMethods;
        $this->shared               = $shared;
    }

    public static function __set_state(array $state) : self
    {
        return new self(
            class               : $state['class'],
            instantiable        : $state['instantiable'] ?? false,
            constructor         : $state['constructor'] ?? null,
            injectableProperties: $state['injectableProperties'] ?? [],
            injectableMethods   : $state['injectableMethods'] ?? [],
            shared              : $state['shared'] ?? false,
            fingerprint         : $state['fingerprint'] ?? '',
        );
    }
}
