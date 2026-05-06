<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

use DateTimeImmutable;

final readonly class TenantSecurityChangeRequest
{
    /**
     * @param  array<string, string>  $diff
     */
    public function __construct(public string $changeId, public string $tenantSlug, public string $requestedBy, public string $reason, public ?TenantSecurityConfiguration $before, public TenantSecurityConfiguration $after, public array $diff, public TenantSecurityChangeRequestStatus $status, public DateTimeImmutable $requestedAt, public ?string $approvedBy = null, public ?DateTimeImmutable $approvedAt = null, public ?DateTimeImmutable $appliedAt = null, public ?DateTimeImmutable $rolledBackAt = null)
    {
    }
}
