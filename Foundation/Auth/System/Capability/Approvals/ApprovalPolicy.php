<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Approvals;

/**
 * Approval policy defining which operations require approval.
 */
final readonly class ApprovalPolicy
{
    /**
     * @param list<ApprovalType> $requiredApprovals
     * @param array<ApprovalType, list<string>> $requiredRoles
     * @param array<ApprovalType, int> $expiryMinutes
     */
    public function __construct(
        public array $requiredApprovals = [],
        public array $requiredRoles = [],
        public array $expiryMinutes = []
    ) {}

    public function requiresApproval(ApprovalType $type) : bool
    {
        return in_array($type, $this->requiredApprovals, true);
    }

    /**
     * @return list<string>
     */
    public function requiredRolesFor(ApprovalType $type) : array
    {
        return $this->requiredRoles[$type->value] ?? [];
    }

    public function expiryMinutesFor(ApprovalType $type) : int
    {
        return $this->expiryMinutes[$type->value] ?? 1440;
    }

    /**
     * Returns default approval policy.
     */
    public static function standard() : self
    {
        return new self(
            requiredApprovals: [
                ApprovalType::CLIENT_REGISTRATION,
                ApprovalType::FEDERATION_CHANGE,
                ApprovalType::TENANT_OWNERSHIP_TRANSFER,
                ApprovalType::ADMIN_ELEVATION,
                ApprovalType::BREAK_GLASS,
                ApprovalType::SECURITY_POLICY_CHANGE,
            ],
            requiredRoles: [
                ApprovalType::CLIENT_REGISTRATION->value => ['security-admin'],
                ApprovalType::FEDERATION_CHANGE->value => ['security-admin'],
                ApprovalType::TENANT_OWNERSHIP_TRANSFER->value => ['tenant-admin'],
                ApprovalType::ADMIN_ELEVATION->value => ['security-admin'],
                ApprovalType::BREAK_GLASS->value => ['security-admin', 'auditor'],
                ApprovalType::SECURITY_POLICY_CHANGE->value => ['security-admin'],
            ],
            expiryMinutes: [
                ApprovalType::CLIENT_REGISTRATION->value => 10080,
                ApprovalType::FEDERATION_CHANGE->value => 10080,
                ApprovalType::TENANT_OWNERSHIP_TRANSFER->value => 1440,
                ApprovalType::ADMIN_ELEVATION->value => 60,
                ApprovalType::BREAK_GLASS->value => 15,
                ApprovalType::SECURITY_POLICY_CHANGE->value => 10080,
            ]
        );
    }
}