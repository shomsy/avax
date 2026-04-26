<?php

declare(strict_types=1);

namespace components\Validation\System\Capabilities\Execution;

use components\DataFoundation\Arrhae;
use RuntimeException;

/**
 * State Owner: Holds the results of a validation process.
 *
 * Part of the Foundation\Validation capability.
 */
final class ValidationResult
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function addError(string $property, string $message) : void
    {
        $this->errors[$property]   ??= [];
        $this->errors[$property][] = $message;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    public function firstError(string $property) : string|null
    {
        return $this->errors[$property][0] ?? null;
    }

    public function throwOnFailure() : void
    {
        if (! $this->isValid()) {
            // We'll define a proper ValidationException later
            throw new RuntimeException(
                message: 'Validation failed: ' . json_encode(value: $this->errors)
            );
        }
    }

    public function isValid() : bool
    {
        return $this->errors === [];
    }
}
