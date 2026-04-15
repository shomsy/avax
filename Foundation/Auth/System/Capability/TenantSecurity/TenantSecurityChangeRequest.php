<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantSecurity;

use DateTimeImmutable;

final readonly class TenantSecurityChangeRequest
{
    public DateTimeImmutable|null            $rolledBackAt;
    public DateTimeImmutable|null            $appliedAt;
    public DateTimeImmutable|null            $approvedAt;
    public string|null                       $approvedBy;
    public DateTimeImmutable                 $requestedAt;
    public TenantSecurityChangeRequestStatus $status;
    public array                             $diff;
    public TenantSecurityConfiguration       $after;
    public TenantSecurityConfiguration|null  $before;
    public string                            $reason;
    public string                            $requestedBy;
    public string                            $tenantSlug;
    public string                            $changeId;

    /**
     * @param array<string, string> $diff
     */
    public function __construct(
        string                            $changeId,
        string                            $tenantSlug,
        string                            $requestedBy,
        string                            $reason,
        TenantSecurityConfiguration|null  $before,
        TenantSecurityConfiguration       $after,
        array                             $diff,
        TenantSecurityChangeRequestStatus $status,
        DateTimeImmutable                 $requestedAt,
        string|null                       $approvedBy = null,
        DateTimeImmutable|null            $approvedAt = null,
        DateTimeImmutable|null            $appliedAt = null,
        DateTimeImmutable|null            $rolledBackAt = null
    )
    {
        $this->changeId     = $changeId;
        $this->tenantSlug   = $tenantSlug;
        $this->requestedBy  = $requestedBy;
        $this->reason       = $reason;
        $this->before       = $before;
        $this->after        = $after;
        $this->diff         = $diff;
        $this->status       = $status;
        $this->requestedAt  = $requestedAt;
        $this->approvedBy   = $approvedBy;
        $this->approvedAt   = $approvedAt;
        $this->appliedAt    = $appliedAt;
        $this->rolledBackAt = $rolledBackAt;
    }
}
