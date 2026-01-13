<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\DTO;

/**
 * Injection Report Data Transfer Object
 *
 * Contains detailed information about dependency injection operations.
 * Used for diagnostics and debugging of injection behavior.
 *
 * @see     docs/Features/Core/DTO/InjectionReport.md
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
