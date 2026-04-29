<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;

final readonly class ServiceBlueprint
{
    public string           $fingerprint;
    public bool             $shared;
    public array            $injectableMethods;
    public array            $injectableProperties;
    public ResolvePlan|null $constructor;
    public bool             $instantiable;
    public string           $class;

    /**
     * @param list<array{name: string, serviceId: string|null, readonly: bool}> $injectableProperties
     * @param list<array{name: string, plan: ResolvePlan}>                      $injectableMethods
     */
    public function __construct(
        string           $class,
        bool|null        $instantiable = null,
        ResolvePlan|null $constructor = null,
        array|null       $injectableProperties = null,
        array|null       $injectableMethods = null,
        bool|null        $shared = null,
        string           $fingerprint = ''
    )
    {
        $instantiable               ??= false;
        $injectableProperties       ??= [];
        $injectableMethods          ??= [];
        $shared                     ??= false;
        $this->class                = $class;
        $this->instantiable         = $instantiable;
        $this->constructor          = $constructor;
        $this->injectableProperties = $injectableProperties;
        $this->injectableMethods    = $injectableMethods;
        $this->shared               = $shared;
        $this->fingerprint          = $fingerprint;
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
            fingerprint         : $state['fingerprint'] ?? ''
        );
    }
}
