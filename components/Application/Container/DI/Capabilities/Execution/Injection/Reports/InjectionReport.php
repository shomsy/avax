<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Reports;

/**
 * Stable public diagnostics DTO for injection inspection.
 */
readonly class InjectionReport
{
    public bool   $success;
    public array  $injectedMethods;
    public array  $injectedProperties;
    public object $target;

    public function __construct(
        object     $target,
        array|null $injectedProperties = null,
        array|null $injectedMethods = null,
        bool       $success = true
    )
    {
        $injectedProperties       ??= [];
        $injectedMethods          ??= [];
        $this->target             = $target;
        $this->injectedProperties = $injectedProperties;
        $this->injectedMethods    = $injectedMethods;
        $this->success            = $success;
    }
}
