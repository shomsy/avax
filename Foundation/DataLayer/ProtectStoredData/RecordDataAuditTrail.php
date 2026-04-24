<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;

enum AuditAction: string
{
    case READ   = 'read';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case EXPORT = 'export';
    case ACCESS = 'access';
}

final readonly class RecordDataAuditTrail
{
    public function __construct(
        private DataAccessPolicy $policy
    ) {}

    public function describeResponsibility() : string
    {
        return 'records data audit trail for all data operations.';
    }

    public function record(
        string      $tenantId,
        string      $userId,
        AuditAction $action,
        string      $resourceType,
        string      $resourceId,
        array       $changes = []
    ) : DataAuditEntry
    {
        if (! $this->policy->auditEnabled) {
            throw new InvalidArgumentException('Auditing is not enabled.');
        }

        return DataAuditEntry::create(
            tenantId    : $tenantId,
            userId      : $userId,
            action      : $action,
            resourceType: $resourceType,
            resourceId  : $resourceId,
            changes     : $changes
        );
    }

    public function toMetadata() : array
    {
        return $this->policy->toMetadata();
    }
}