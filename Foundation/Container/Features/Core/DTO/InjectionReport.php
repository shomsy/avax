<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\DTO;

/**
 * Diagnostic report for object injection.
 */
final readonly class InjectionReport
{
    public function __construct(
        public bool   $success,
        public string $class,
        /** @var array<string, string> propertyName => type */
        public array  $properties,
        /** @var array<string, array> methodName => parameters */
        public array  $methods,
        /** @var string[] List of errors encountered */
        public array  $errors = []
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
