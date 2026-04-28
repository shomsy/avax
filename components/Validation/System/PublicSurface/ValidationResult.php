<?php

declare(strict_types=1);

namespace Avax\Components\Validation\System\PublicSurface;

final class ValidationResult
{
    public function __construct(
        public readonly array $errors = [],
    ) {
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }
}