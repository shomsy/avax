<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Injection;

/**
 * Stable public diagnostics DTO for injection inspection.
 */
readonly class InjectionReport
{
    public function __construct(
        public object $target,
        public array $injectedProperties = [],
        public array $injectedMethods = [],
        public bool $success = true,
        public array $errors = []
    ) {}
}
