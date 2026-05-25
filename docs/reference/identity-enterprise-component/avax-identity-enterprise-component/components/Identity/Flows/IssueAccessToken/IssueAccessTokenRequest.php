<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\IssueAccessToken;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;
use DateInterval;

final readonly class IssueAccessTokenRequest
{
    /** @param list<string> $scopes */
    public function __construct(private UserId $userId, private DateInterval $ttl, private TenantId|null $tenantId = null, private array $scopes = []) {}

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

    /** @return list<string> */
    public function scopes(): array
    {
        return $this->scopes;
    }
}
