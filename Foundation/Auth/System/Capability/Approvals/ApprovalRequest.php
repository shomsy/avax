<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Approvals;

use DateTimeImmutable;

/**
 * Approval request status.
 */
enum ApprovalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}

/**
 * Types of resources that can require approval.
 */
enum ApprovalType: string
{
    case CLIENT_REGISTRATION = 'client_registration';
    case FEDERATION_CHANGE = 'federation_change';
    case SCIM_MAPPING_CHANGE = 'scim_mapping_change';
    case ADMIN_ELEVATION = 'admin_elevation';
    case TENANT_OWNERSHIP_TRANSFER = 'tenant_ownership_transfer';
    case BREAK_GLASS = 'break_glass';
    case SECURITY_POLICY_CHANGE = 'security_policy_change';
    case SENSITIVE_DATA_ACCESS = 'sensitive_data_access';
}

/**
 * Approval request for security-sensitive operations.
 */
final readonly class ApprovalRequest
{
    public function __construct(
        public string $requestId,
        public ApprovalType $type,
        public string $requesterId,
        public string $resourceId,
        public string $description,
        public ApprovalStatus $status,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable|null $expiresAt,
        public DateTimeImmutable|null $resolvedAt,
        public string|null $resolverId,
        public string|null $resolutionReason
    ) {}

    public function isPending() : bool
    {
        return $this->status === ApprovalStatus::PENDING;
    }

    public function isExpired(DateTimeImmutable $now) : bool
    {
        return $this->expiresAt !== null && $this->expiresAt < $now;
    }

    public function canBeApproved() : bool
    {
        return $this->status === ApprovalStatus::PENDING;
    }

    public function requiresApproval(ApprovalPolicy $policy) : bool
    {
        return $policy->requiresApproval($this->type);
    }
}