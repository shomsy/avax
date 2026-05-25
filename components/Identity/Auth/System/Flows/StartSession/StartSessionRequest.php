<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\StartSession;

use Avax\Components\Identity\Auth\System\Foundation\Ids\TenantId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\UserId;
use DateInterval;

/**
 * StartSessionRequest — DTO carrying input for starting a user session.
 *
 * Adapted from the enterprise reference package.
 */
final readonly class StartSessionRequest
{
    public function __construct(
        private UserId $userId,
        private DateInterval $ttl,
        private TenantId|null $tenantId = null,
    ) {}

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
