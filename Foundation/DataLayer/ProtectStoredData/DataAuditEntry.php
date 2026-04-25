<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;
use SensitiveParameter;

enum AuditAction: string
{
    case READ   = 'read';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case EXPORT = 'export';
    case ACCESS = 'access';
}

final readonly class DataAuditEntry
{
    public function __construct(
        public string      $id,
        public string      $tenantId,
        public string      $userId,
        public AuditAction $action,
        public string      $resourceType,
        public string      $resourceId,
        public array       $changes,
        public float       $timestamp,
        #[SensitiveParameter] public string $ipAddress
    )
    {
        if (empty($this->id)) {
            throw new InvalidArgumentException(message: 'Audit entry ID cannot be empty.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'records data audit trail entry with user, action, resource, and timestamp.';
    }

    public static function create(
        string      $tenantId,
        string      $userId,
        AuditAction $action,
        string      $resourceType,
        string      $resourceId,
        array       $changes = []
    ) : self
    {
        return new self(
            id          : bin2hex(random_bytes(16)),
            tenantId    : $tenantId,
            userId      : $userId,
            action      : $action,
            resourceType: $resourceType,
            resourceId  : $resourceId,
            changes     : $changes,
            timestamp   : microtime(true),
            ipAddress   : $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );
    }

    public function isSensitiveChange() : bool
    {
        return ! empty(array_intersect_key(
            $this->changes,
            ['password' => 1, 'ssn' => 1, 'credit_card' => 1]
        ));
    }

    public function toMetadata() : array
    {
        return [
            'id'            => $this->id,
            'tenant_id'     => $this->tenantId,
            'user_id'       => $this->userId,
            'action'        => $this->action->value,
            'resource_type' => $this->resourceType,
            'resource_id'   => $this->resourceId,
            'changes'       => $this->changes,
            'timestamp'     => $this->timestamp,
            'ip_address'    => $this->ipAddress,
        ];
    }
}