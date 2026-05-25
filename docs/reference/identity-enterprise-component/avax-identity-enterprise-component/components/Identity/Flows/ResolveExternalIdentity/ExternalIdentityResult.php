<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\ResolveExternalIdentity;

use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class ExternalIdentityResult
{
    private function __construct(private UserId|null $userId) {}

    public static function found(UserId $userId): self
    {
        return new self($userId);
    }

    public static function missing(): self
    {
        return new self(null);
    }

    public function userId(): UserId|null
    {
        return $this->userId;
    }
}
