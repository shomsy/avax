<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\AuthenticatePassword;

use Avax\Components\Identity\Foundation\Values\LoginName;
use Avax\Components\Identity\Foundation\Values\PlainPassword;
use Avax\Components\Identity\Foundation\Values\TenantId;

final readonly class PasswordLogin
{
    public function __construct(private LoginName $loginName, private PlainPassword $password, private TenantId|null $tenantId = null) {}

    public function loginName(): LoginName
    {
        return $this->loginName;
    }

    public function password(): PlainPassword
    {
        return $this->password;
    }

    public function tenantId(): TenantId|null
    {
        return $this->tenantId;
    }
}
