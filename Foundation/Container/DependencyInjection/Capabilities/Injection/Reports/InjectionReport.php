<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Injection\Reports;

/**
 * Injection Report Data Transfer Object
 *
 * Contains detailed information about dependency injection operations.
 * Used for diagnostics and debugging of injection behavior.
 *
 */
final readonly class InjectionReport
{
    public function __construct(
        public object $target,
        public array  $injectedProperties = [],
        public array  $injectedMethods = [],
        public bool   $success = true,
        public array  $errors = []
    ) {}
}
