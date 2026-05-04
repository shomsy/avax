<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Rules;

/**
 * ValidationFailure DTO - represents the result of a validation operation.
 */
final readonly class ValidationFailure
{
    /** @param array<string, list<string>> $errors */
    public function __construct(
        public array $errors = [],
    )
    {
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function getError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /** @return list<string> */
    public function getErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /** @return array<string, list<string>> */
    public function all(): array
    {
        return $this->errors;
    }

    public function first(): ?string
    {
        foreach ($this->errors as $error) {
            return $error[0];
        }

        return null;
    }
}
