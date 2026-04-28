<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security;

use DateTimeImmutable;

final readonly class TenantSecurityChangeRequest
{
    /**
     * @param array<string, string> $diff
     */
    public function __construct(public string $changeId, public string $tenantSlug, public string $requestedBy, public string $reason, public TenantSecurityConfiguration|null $before, public TenantSecurityConfiguration $after, public array $diff, public TenantSecurityChangeRequestStatus $status, public DateTimeImmutable $requestedAt, public string|null $approvedBy = null, public DateTimeImmutable|null $approvedAt = null, public DateTimeImmutable|null $appliedAt = null, public DateTimeImmutable|null $rolledBackAt = null) {}
}
