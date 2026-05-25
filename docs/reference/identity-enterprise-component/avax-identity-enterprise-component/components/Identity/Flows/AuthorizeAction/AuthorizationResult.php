<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\AuthorizeAction;

final readonly class AuthorizationResult
{
    private function __construct(private bool $allowed) {}

    public static function allowed(): self
    {
        return new self(true);
    }

    public static function denied(): self
    {
        return new self(false);
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }
}
