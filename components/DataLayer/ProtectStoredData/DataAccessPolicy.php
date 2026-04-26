<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class DataAccessPolicy
{
    public function __construct(
        public string $name,
        public array  $allowedOperations,
        public array  $maskingRules,
        public bool   $auditEnabled
    ) {}

    public static function standard() : self
    {
        return new self(
            name             : 'standard',
            allowedOperations: ['read', 'write'],
            maskingRules     : ['ssn' => 'partial', 'password' => 'full'],
            auditEnabled     : true
        );
    }

    public function describeResponsibility() : string
    {
        return 'records data access policy including operations, masking, and audit settings.';
    }

    public function allows(string $operation) : bool
    {
        return in_array($operation, $this->allowedOperations, true);
    }

    public function toMetadata() : array
    {
        return [
            'name'               => $this->name,
            'allowed_operations' => $this->allowedOperations,
            'masking_rules'      => $this->maskingRules,
            'audit_enabled'      => $this->auditEnabled,
        ];
    }
}