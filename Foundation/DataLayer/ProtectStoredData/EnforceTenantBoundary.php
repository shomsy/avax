<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use InvalidArgumentException;

enum TenantBoundaryType: string
{
    case SCHEMA   = 'schema';
    case ROW      = 'row';
    case DATABASE = 'database';
    case CLUSTER  = 'cluster';
}

final readonly class EnforceTenantBoundary
{
    public function __construct(
        public TenantBoundaryType $type,
        public string             $tenantIdColumn,
        public bool               $strict
    ) {}

    public function describeResponsibility() : string
    {
        return 'enforces tenant boundary as a system rule.';
    }

    public static function rowLevel(string $tenantIdColumn = 'tenant_id') : self
    {
        return new self(TenantBoundaryType::ROW, $tenantIdColumn, true);
    }

    public static function schemaLevel(string $tenantIdColumn = 'tenant_id') : self
    {
        return new self(TenantBoundaryType::SCHEMA, $tenantIdColumn, true);
    }

    public function enforceForQuery(string $tenantId) : WhereClause
    {
        return new WhereClause(
            column  : $this->tenantIdColumn,
            operator: '=',
            value   : $tenantId
        );
    }

    public function toMetadata() : array
    {
        return [
            'type'          => $this->type->value,
            'tenant_column' => $this->tenantIdColumn,
            'strict'        => $this->strict,
        ];
    }
}

final readonly class WhereClause
{
    public function __construct(
        public string $column,
        public string $operator,
        public mixed  $value
    ) {}

    public function toSql() : string
    {
        return sprintf('%s %s :%s', $this->column, $this->operator, $this->column);
    }
}