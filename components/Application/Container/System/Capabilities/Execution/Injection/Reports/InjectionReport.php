<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Reports;

/**
 * Stable public diagnostics DTO for injection inspection.
 */
readonly class InjectionReport
{
    public array $injectedMethods;

    public array $injectedProperties;

    public function __construct(
        public object $target,
        ?array $injectedProperties = null,
        ?array $injectedMethods = null,
        public bool $success = true,
    ) {
        $injectedProperties ??= [];
        $injectedMethods    ??= [];
        $this->injectedProperties = $injectedProperties;
        $this->injectedMethods    = $injectedMethods;
    }
}
