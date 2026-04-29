<?php
declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Execution;

final class ValidationResult
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    public function addError(string $property, string $message): void
    {
        $this->errors[$property] ??= [];
        $this->errors[$property][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
