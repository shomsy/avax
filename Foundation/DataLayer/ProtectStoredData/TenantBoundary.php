<?php

declare(strict_types=1);

namespace Avax\DataLayer\ProtectStoredData;

use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;

enum SensitiveField: string
{
    case PASSWORD      = 'password';
    case SECRET        = 'secret';
    case TOKEN         = 'token';
    case API_KEY       = 'api_key';
    case CREDIT_CARD   = 'credit_card';
    case SSN           = 'ssn';
    case EMAIL         = 'email';
    case PHONE         = 'phone';
    case ADDRESS       = 'address';
    case DATE_OF_BIRTH = 'date_of_birth';
    case PERSONAL_ID   = 'personal_id';

    public function classify() : string
    {
        return match ($this) {
            self::PASSWORD, self::SECRET, self::TOKEN, self::API_KEY                        => 'credential',
            self::CREDIT_CARD, self::SSN                                                    => 'financial',
            self::EMAIL, self::PHONE, self::ADDRESS, self::DATE_OF_BIRTH, self::PERSONAL_ID => 'pii',
        };
    }

    public function canLog() : bool
    {
        return false;
    }

    public function canQuery() : bool
    {
        return false;
    }

    public function maskPattern() : string
    {
        return match ($this) {
            self::PASSWORD      => '******',
            self::SECRET        => '******',
            self::TOKEN         => '****',
            self::API_KEY       => '****',
            self::CREDIT_CARD   => '****-****-****-****',
            self::SSN           => '***-**-****',
            self::EMAIL         => '***@***.***',
            self::PHONE         => '***-***-****',
            self::ADDRESS       => '***',
            self::DATE_OF_BIRTH => '**/**/****',
            self::PERSONAL_ID   => '********',
        };
    }
}

final readonly class TenantBoundary
{
    public string  $id;
    public string  $column;
    public bool        $isRequired;
    public string|null $parentTenantId;
    public array       $settings;

    private function __construct(
        string      $id,
        string|null $column = null,
        bool|null   $isRequired = null,
        string|null $parentTenantId = null,
        array       $settings = []
    )
    {
        $column     ??= 'tenant_id';
        $isRequired ??= true;
        $this->id             = $id;
        $this->column         = $column;
        $this->isRequired     = $isRequired;
        $this->parentTenantId = $parentTenantId;
        $this->settings       = $settings;
    }

    public static function create(
        string $id,
        array  $options = []
    ) : self
    {
        if (empty(trim($id))) {
            throw new InvalidArgumentException(message: 'Tenant ID cannot be empty.');
        }

        return new self(
            id            : $id,
            column        : $options['column'] ?? 'tenant_id',
            isRequired    : $options['required'] ?? true,
            parentTenantId: $options['parent'] ?? null,
            settings      : $options
        );
    }

    public static function multiTenant(string $column = 'tenant_id') : self
    {
        return self::create(id: 'multi', options: ['column' => $column, 'required' => true]);
    }

    public static function singleTenant(string $column = 'tenant_id') : self
    {
        return self::create(id: 'single', options: ['column' => $column, 'required' => true]);
    }

    public function toWhereClause(mixed $tenantId) : string
    {
        return sprintf('%s = %s', $this->column, is_int($tenantId) ? $tenantId : "'" . addslashes($tenantId) . "'");
    }

    public function withDefault(mixed $defaultTenantId) : self
    {
        return new self(
            id            : $this->id,
            column        : $this->column,
            isRequired    : $this->isRequired,
            parentTenantId: $this->parentTenantId,
            settings      : array_merge($this->settings, ['default' => $defaultTenantId])
        );
    }

    public function isMultiTenant() : bool
    {
        return $this->id === 'multi';
    }
}

final readonly class DataAccessPolicy
{
    public string               $name;
    public TenantBoundary|null  $tenantBoundary;
    public array                $allowedOperations;
    public array            $sensitiveFields;
    public bool                 $enableAudit;
    public RetentionPolicy|null $retentionPolicy;

    private function __construct(
        string               $name,
        TenantBoundary|null  $tenantBoundary = null,
        array|null           $allowedOperations = null,
        array|null           $sensitiveFields = null,
        bool|null            $enableAudit = null,
        RetentionPolicy|null $retentionPolicy = null
    )
    {
        $allowedOperations ??= [];
        $sensitiveFields   ??= [];
        $enableAudit       ??= false;
        $this->name              = $name;
        $this->tenantBoundary    = $tenantBoundary;
        $this->allowedOperations = $allowedOperations;
        $this->sensitiveFields   = $sensitiveFields;
        $this->enableAudit       = $enableAudit;
        $this->retentionPolicy   = $retentionPolicy;
    }

    public static function create(string $name, array $options = []) : self
    {
        $tenantBoundary = isset($options['tenant'])
            ? TenantBoundary::create(id: $options['tenant'])
            : null;

        return new self(
            name             : $name,
            tenantBoundary   : $tenantBoundary,
            allowedOperations: $options['allowed'] ?? ['select', 'insert', 'update', 'delete'],
            sensitiveFields  : $options['sensitive'] ?? [],
            enableAudit      : $options['audit'] ?? false,
            retentionPolicy  : isset($options['retention'])
                                   ? RetentionPolicy::create($options['retention'])
                                   : null
        );
    }

    public static function strict() : self
    {
        return new self(
            name             : 'strict',
            tenantBoundary   : TenantBoundary::multiTenant(),
            allowedOperations: ['select', 'insert', 'update', 'delete'],
            sensitiveFields  : SensitiveField::cases(),
            enableAudit      : true,
            retentionPolicy  : RetentionPolicy::days(90)
        );
    }

    public static function relaxed() : self
    {
        return new self(
            name             : 'relaxed',
            allowedOperations: ['select', 'insert', 'update', 'delete']
        );
    }

    public function requiresTenant() : bool
    {
        return $this->tenantBoundary !== null && $this->tenantBoundary->isRequired;
    }

    public function isOperationAllowed(string $operation) : bool
    {
        return in_array(strtolower($operation), $this->allowedOperations, true);
    }

    public function isFieldSensitive(string $field) : bool
    {
        foreach ($this->sensitiveFields as $sensitive) {
            if ($sensitive instanceof SensitiveField && $sensitive->value === $field) {
                return true;
            }
            if (is_string($sensitive) && $sensitive === $field) {
                return true;
            }
        }

        return false;
    }

    public function addSensitiveField(SensitiveField $field) : self
    {
        $fields   = $this->sensitiveFields;
        $fields[] = $field;

        return new self(
            name             : $this->name,
            tenantBoundary   : $this->tenantBoundary,
            allowedOperations: $this->allowedOperations,
            sensitiveFields  : $fields,
            enableAudit      : $this->enableAudit,
            retentionPolicy  : $this->retentionPolicy
        );
    }

    public function toArray() : array
    {
        return [
            'name'      => $this->name,
            'tenant'    => $this->tenantBoundary?->toArray(),
            'allowed'   => $this->allowedOperations,
            'sensitive' => array_map(static fn (SensitiveField $f) => $f->value, $this->sensitiveFields),
            'audit'     => $this->enableAudit,
            'retention' => $this->retentionPolicy?->toArray(),
        ];
    }
}

final readonly class DataAuditEntry
{
    public string             $id;
    public string             $action;
    public string            $table;
    public string|null       $recordId;
    public string|null       $tenantId;
    public array             $oldValues;
    public array              $newValues;
    public string            $actorId;
    public string|null       $actorIp;
    public DateTimeImmutable $timestamp;
    public bool              $success;
    public string|null       $errorMessage;

    private function __construct(
        string            $id,
        string            $action,
        string            $table,
        string|null       $recordId,
        string|null       $tenantId,
        array             $oldValues,
        array             $newValues,
        string            $actorId,
        string|null       $actorIp,
        DateTimeImmutable $timestamp,
        bool|null         $success = null,
        string|null       $errorMessage = null
    )
    {
        $success         ??= true;
        $this->id        = $id;
        $this->action    = $action;
        $this->table     = $table;
        $this->recordId  = $recordId;
        $this->tenantId  = $tenantId;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
        $this->actorId   = $actorId;
        $this->actorIp   = $actorIp;
        $this->timestamp = $timestamp;
        $this->success   = $success;
        $this->errorMessage = $errorMessage;
    }

    public static function create(
        string      $action,
        string      $table,
        string|null $recordId,
        array       $oldValues,
        array       $newValues,
        string      $actorId,
        array       $options = []
    ) : self
    {
        return new self(
            id          : self::generateId(),
            action      : $action,
            table       : $table,
            recordId    : $recordId,
            tenantId    : $options['tenant_id'] ?? null,
            oldValues   : $oldValues,
            newValues   : $newValues,
            actorId     : $actorId,
            actorIp     : $options['ip'] ?? null,
            timestamp   : new DateTimeImmutable(),
            success     : $options['success'] ?? true,
            errorMessage: $options['error'] ?? null
        );
    }

    public function toInsertSql() : string
    {
        return sprintf(
            "INSERT INTO audit_log (id, action, table_name, record_id, tenant_id, old_values, new_values, actor_id, actor_ip, timestamp, success, error_message) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s)",
            $this->quoted(value: $this->id),
            $this->quoted(value: $this->action),
            $this->quoted(value: $this->table),
            $this->quoted(value: $this->recordId),
            $this->quoted(value: $this->tenantId),
            $this->quoted(value: json_encode($this->oldValues)),
            $this->quoted(value: json_encode($this->newValues)),
            $this->quoted(value: $this->actorId),
            $this->quoted(value: $this->actorIp),
            $this->quoted(value: $this->timestamp->format(format: 'Y-m-d H:i:s.u')),
            $this->success ? 1 : 0,
            $this->quoted(value: $this->errorMessage)
        );
    }

    /**
     * @throws RandomException
     */
    private static function generateId() : string
    {
        return sprintf('audit_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }

    private function quoted(mixed $value) : string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return sprintf("'%s'", addslashes((string) $value));
    }
}