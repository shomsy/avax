<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Accounts;

use Avax\Components\Identity\Foundation\Values\LoginName;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class UserAccount
{
    public function __construct(
        private UserId $userId,
        private LoginName $loginName,
        private bool $enabled = true,
        private TenantId|null $primaryTenantId = null,
    ) {}

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function loginName(): LoginName
    {
        return $this->loginName;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function primaryTenantId(): TenantId|null
    {
        return $this->primaryTenantId;
    }
}
