<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\StartSession;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;
use DateInterval;

final readonly class StartSessionRequest
{
    public function __construct(private UserId $userId, private DateInterval $ttl, private TenantId|null $tenantId = null) {}

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function ttl(): DateInterval
    {
        return $this->ttl;
    }

    public function tenantId(): TenantId|null
    {
        return $this->tenantId;
    }
}
