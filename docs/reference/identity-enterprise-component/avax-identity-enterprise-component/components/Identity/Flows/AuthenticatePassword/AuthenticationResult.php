<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\AuthenticatePassword;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class AuthenticationResult
{
    private function __construct(private bool $authenticated, private UserId|null $userId = null, private TenantId|null $tenantId = null) {}

    public static function accepted(UserId $userId, TenantId|null $tenantId = null): self
    {
        return new self(true, $userId, $tenantId);
    }

    public static function rejected(): self
    {
        return new self(false);
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function userId(): UserId|null
    {
        return $this->userId;
    }

    public function tenantId(): TenantId|null
    {
        return $this->tenantId;
    }
}
