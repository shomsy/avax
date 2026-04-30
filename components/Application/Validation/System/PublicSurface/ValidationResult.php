<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\PublicSurface;

final readonly class ValidationResult
{
    public function __construct(
        public array $errors = [],
    ) {}

    public function fails() : bool
    {
        return $this->errors !== [];
    }

    public function passes() : bool
    {
        return $this->errors === [];
    }
}
