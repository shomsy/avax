<?php

declare(strict_types=1);

namespace Avax\DataLayer\ShapeStoredData;

use InvalidArgumentException;

enum ConstraintType: string
{
    case PRIMARY_KEY = 'PRIMARY KEY';
    case UNIQUE      = 'UNIQUE';
    case FOREIGN_KEY = 'FOREIGN KEY';
    case CHECK       = 'CHECK';
    case NOT_NULL    = 'NOT NULL';
}

enum ConstraintDeferrability: string
{
    case IMMEDIATE  = 'IMMEDIATE';
    case DEFERRED   = 'DEFERRED';
    case DEFERRABLE = 'DEFERRABLE';
}

final readonly class StoredConstraint
{
    public string                  $name;
    public ConstraintType          $type;
    public string                  $tableName;
    public array                   $columns;
    public string|null             $referenceTable;
    public array|null              $referenceColumns;
    public OnDeleteAction|null     $onDelete;
    public OnUpdateAction|null     $onUpdate;
    public string|null             $checkExpression;
    public ConstraintDeferrability $deferrability;
    public bool                    $isEnabled;
    public string|null             $comment;
    public int|null                $version;

    private function __construct(
        string                       $name,
        ConstraintType               $type,
        string                       $tableName,
        array|null                   $columns = null,
        string|null                  $referenceTable = null,
        array|null                   $referenceColumns = null,
        OnDeleteAction|null          $onDelete = null,
        OnUpdateAction|null          $onUpdate = null,
        string|null                  $checkExpression = null,
        ConstraintDeferrability|null $deferrability = null,
        bool|null                    $isEnabled = null,
        string|null                  $comment = null,
        int|null                     $version = null
    )
    {
        $columns                ??= [];
        $deferrability          ??= ConstraintDeferrability::IMMEDIATE;
        $isEnabled              ??= true;
        $this->name             = $name;
        $this->type             = $type;
        $this->tableName        = $tableName;
        $this->columns          = $columns;
        $this->referenceTable   = $referenceTable;
        $this->referenceColumns = $referenceColumns;
        $this->onDelete         = $onDelete;
        $this->onUpdate         = $onUpdate;
        $this->checkExpression  = $checkExpression;
        $this->deferrability    = $deferrability;
        $this->isEnabled        = $isEnabled;
        $this->comment          = $comment;
        $this->version          = $version;
    }

    public static function primaryKey(
        string $tableName,
        array  $columns,
        array  $options = []
    ) : self
    {
        return self::create(
            name     : $options['name'] ?? sprintf('%s_pkey', $tableName),
            type     : ConstraintType::PRIMARY_KEY,
            tableName: $tableName,
            options  : array_merge($options, ['columns' => $columns])
        );
    }

    public static function create(
        string         $name,
        ConstraintType $type,
        string         $tableName,
        array          $options = []
    ) : self
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException(message: 'Constraint name cannot be empty.');
        }

        if ($type === ConstraintType::FOREIGN_KEY) {
            if (empty($options['reference_table'])) {
                throw new InvalidArgumentException(message: 'Foreign key constraint requires reference_table.');
            }
            if (empty($options['reference_columns'])) {
                throw new InvalidArgumentException(message: 'Foreign key constraint requires reference_columns.');
            }
        }

        if ($type === ConstraintType::CHECK && empty($options['check'])) {
            throw new InvalidArgumentException(message: 'Check constraint requires check expression.');
        }

        return new self(
            name            : $name,
            type            : $type,
            tableName       : $tableName,
            columns         : $options['columns'] ?? [],
            referenceTable  : $options['reference_table'] ?? null,
            referenceColumns: $options['reference_columns'] ?? null,
            onDelete        : isset($options['on_delete'])
                                  ? OnDeleteAction::from(value: $options['on_delete'])
                                  : null,
            onUpdate        : isset($options['on_update'])
                                  ? OnUpdateAction::from(value: $options['on_update'])
                                  : null,
            checkExpression : $options['check'] ?? null,
            deferrability   : ConstraintDeferrability::from(
                                  value: $options['deferrable'] ?? 'IMMEDIATE'
                              ),
            isEnabled       : $options['enabled'] ?? true,
            comment         : $options['comment'] ?? null,
            version         : $options['version'] ?? null
        );
    }

    public static function unique(
        string $name,
        string $tableName,
        array  $columns,
        array  $options = []
    ) : self
    {
        return self::create(
            name     : $name,
            type     : ConstraintType::UNIQUE,
            tableName: $tableName,
            options  : array_merge($options, ['columns' => $columns])
        );
    }

    public static function foreignKey(
        string $name,
        string $tableName,
        array  $columns,
        string $referenceTable,
        array  $referenceColumns,
        array  $options = []
    ) : self
    {
        return self::create(
            name     : $name,
            type     : ConstraintType::FOREIGN_KEY,
            tableName: $tableName,
            options  : array_merge($options, [
                           'columns'           => $columns,
                           'reference_table'   => $referenceTable,
                           'reference_columns' => $referenceColumns,
                       ])
        );
    }

    public static function check(
        string $name,
        string $tableName,
        string $expression,
        array  $options = []
    ) : self
    {
        return self::create(
            name     : $name,
            type     : ConstraintType::CHECK,
            tableName: $tableName,
            options  : array_merge($options, ['check' => $expression])
        );
    }

    public static function notNull(
        string $tableName,
        string $column,
        array  $options = []
    ) : self
    {
        return self::create(
            name     : $options['name'] ?? sprintf('%s_%s_not_null', $tableName, $column),
            type     : ConstraintType::NOT_NULL,
            tableName: $tableName,
            options  : array_merge($options, ['columns' => [$column]])
        );
    }

    public function enable() : self
    {
        return new self(
            name            : $this->name,
            type            : $this->type,
            tableName       : $this->tableName,
            columns         : $this->columns,
            referenceTable  : $this->referenceTable,
            referenceColumns: $this->referenceColumns,
            onDelete        : $this->onDelete,
            onUpdate        : $this->onUpdate,
            checkExpression : $this->checkExpression,
            deferrability   : $this->deferrability,
            isEnabled       : true,
            comment         : $this->comment,
            version         : $this->version
        );
    }

    public function disable() : self
    {
        return new self(
            name            : $this->name,
            type            : $this->type,
            tableName       : $this->tableName,
            columns         : $this->columns,
            referenceTable  : $this->referenceTable,
            referenceColumns: $this->referenceColumns,
            onDelete        : $this->onDelete,
            onUpdate        : $this->onUpdate,
            checkExpression : $this->checkExpression,
            deferrability   : $this->deferrability,
            isEnabled       : false,
            comment         : $this->comment,
            version         : $this->version
        );
    }

    public function deferrable(ConstraintDeferrability $deferrability) : self
    {
        return new self(
            name            : $this->name,
            type            : $this->type,
            tableName       : $this->tableName,
            columns         : $this->columns,
            referenceTable  : $this->referenceTable,
            referenceColumns: $this->referenceColumns,
            onDelete        : $this->onDelete,
            onUpdate        : $this->onUpdate,
            checkExpression : $this->checkExpression,
            deferrability   : $deferrability,
            isEnabled       : $this->isEnabled,
            comment         : $this->comment,
            version         : $this->version
        );
    }

    public function toAlterTableSQL() : string
    {
        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s %s',
            $this->tableName,
            $this->name,
            $this->toConstraintSQL()
        );
    }

    public function toConstraintSQL() : string
    {
        if (! $this->isEnabled) {
            return '';
        }

        return match ($this->type) {
            ConstraintType::PRIMARY_KEY => sprintf(
                'PRIMARY KEY (%s)',
                implode(', ', $this->columns)
            ),
            ConstraintType::UNIQUE      => sprintf(
                'UNIQUE (%s)',
                implode(', ', $this->columns)
            ),
            ConstraintType::FOREIGN_KEY => $this->toForeignKeySQL(),
            ConstraintType::CHECK       => sprintf(
                'CHECK (%s)',
                $this->checkExpression
            ),
            ConstraintType::NOT_NULL    => sprintf(
                'NOT NULL (%s)',
                implode(', ', $this->columns)
            ),
        };
    }

    private function toForeignKeySQL() : string
    {
        $sql = sprintf(
            'FOREIGN KEY (%s) REFERENCES %s(%s)',
            implode(', ', $this->columns),
            $this->referenceTable,
            implode(', ', $this->referenceColumns ?? [])
        );

        if ($this->onDelete !== null) {
            $sql .= sprintf(' ON DELETE %s', $this->onDelete->value);
        }

        if ($this->onUpdate !== null) {
            $sql .= sprintf(' ON UPDATE %s', $this->onUpdate->value);
        }

        return $sql;
    }

    public function toDropSQL() : string
    {
        return sprintf(
            'ALTER TABLE %s DROP CONSTRAINT %s',
            $this->tableName,
            $this->name
        );
    }
}