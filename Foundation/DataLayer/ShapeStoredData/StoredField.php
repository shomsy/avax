<?php

declare(strict_types=1);

namespace Avax\DataLayer\ShapeStoredData;

use InvalidArgumentException;

enum StoredFieldType: string
{
    case STRING    = 'string';
    case TEXT      = 'text';
    case INTEGER   = 'integer';
    case BIGINT    = 'bigint';
    case DECIMAL   = 'decimal';
    case FLOAT     = 'float';
    case BOOLEAN   = 'boolean';
    case DATE      = 'date';
    case DATETIME  = 'datetime';
    case TIMESTAMP = 'timestamp';
    case JSON      = 'json';
    case UUID      = 'uuid';
    case EMAIL     = 'email';
    case URL       = 'url';
}

enum FieldNullability: string
{
    case NULLABLE     = 'nullable';
    case NOT_NULLABLE = 'not_nullable';
}

enum FieldGeneratedType: string
{
    case NONE           = 'none';
    case AUTO_INCREMENT = 'auto_increment';
    case SEQUENCE       = 'sequence';
    case GENERATED      = 'generated';
}

final readonly class StoredField
{
    public string             $name;
    public StoredFieldType    $type;
    public FieldNullability   $nullability;
    public FieldGeneratedType $generatedType;
    public int|null    $length;
    public int|null    $precision;
    public int|null    $scale;
    public mixed              $defaultValue;
    public bool               $isPrimaryKey;
    public bool               $isUnique;
    public string|null $checkConstraint;

    private function __construct(
        string                  $name,
        StoredFieldType         $type,
        FieldNullability        $nullability,
        FieldGeneratedType|null $generatedType = null,
        int|null    $length = null,
        int|null    $precision = null,
        int|null    $scale = null,
        mixed                   $defaultValue = null,
        bool|null               $isPrimaryKey = null,
        bool|null               $isUnique = null,
        string|null $checkConstraint = null
    )
    {
        $generatedType ??= FieldGeneratedType::NONE;
        $isPrimaryKey  ??= false;
        $isUnique      ??= false;
        $this->name            = $name;
        $this->type            = $type;
        $this->nullability     = $nullability;
        $this->generatedType   = $generatedType;
        $this->length          = $length;
        $this->precision       = $precision;
        $this->scale           = $scale;
        $this->defaultValue    = $defaultValue;
        $this->isPrimaryKey    = $isPrimaryKey;
        $this->isUnique        = $isUnique;
        $this->checkConstraint = $checkConstraint;
    }

    public static function create(
        string                $name,
        StoredFieldType       $type,
        FieldNullability|null $nullability = null,
        array                 $options = []
    ) : self
    {
        $nullability ??= FieldNullability::NULLABLE;
        if (empty(trim($name))) {
            throw new InvalidArgumentException(message: 'Field name cannot be empty.');
        }

        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException(
                message: sprintf('Invalid field name "%s". Must start with a letter and contain only alphanumeric characters and underscores.', $name)
            );
        }

        return new self(
            name           : $name,
            type           : $type,
            nullability    : $nullability,
            generatedType  : FieldGeneratedType::from(value: $options['generated'] ?? 'none'),
            length         : $options['length'] ?? null,
            precision      : $options['precision'] ?? null,
            scale          : $options['scale'] ?? null,
            defaultValue   : $options['default'] ?? null,
            isPrimaryKey   : $options['primary_key'] ?? false,
            isUnique       : $options['unique'] ?? false,
            checkConstraint: $options['check'] ?? null
        );
    }

    public function withType(StoredFieldType $type) : self
    {
        return new self(
            name           : $this->name,
            type           : $type,
            nullability    : $this->nullability,
            generatedType  : $this->generatedType,
            length         : $this->length,
            precision      : $this->precision,
            scale          : $this->scale,
            defaultValue   : $this->defaultValue,
            isPrimaryKey   : $this->isPrimaryKey,
            isUnique       : $this->isUnique,
            checkConstraint: $this->checkConstraint
        );
    }

    public function nullable() : self
    {
        return $this->withNullability(nullability: FieldNullability::NULLABLE);
    }

    public function notNullable() : self
    {
        return $this->withNullability(nullability: FieldNullability::NOT_NULLABLE);
    }

    public function withNullability(FieldNullability $nullability) : self
    {
        return new self(
            name           : $this->name,
            type           : $this->type,
            nullability    : $nullability,
            generatedType  : $this->generatedType,
            length         : $this->length,
            precision      : $this->precision,
            scale          : $this->scale,
            defaultValue   : $this->defaultValue,
            isPrimaryKey   : $this->isPrimaryKey,
            isUnique       : $this->isUnique,
            checkConstraint: $this->checkConstraint
        );
    }

    public function asPrimaryKey() : self
    {
        return new self(
            name           : $this->name,
            type           : $this->type,
            nullability    : FieldNullability::NOT_NULLABLE,
            generatedType  : $this->generatedType,
            length         : $this->length,
            precision      : $this->precision,
            scale          : $this->scale,
            defaultValue   : null,
            isPrimaryKey   : true,
            isUnique       : true,
            checkConstraint: $this->checkConstraint
        );
    }

    public function unique() : self
    {
        return new self(
            name           : $this->name,
            type           : $this->type,
            nullability    : $this->nullability,
            generatedType  : $this->generatedType,
            length         : $this->length,
            precision      : $this->precision,
            scale          : $this->scale,
            defaultValue   : $this->defaultValue,
            isPrimaryKey   : $this->isPrimaryKey,
            isUnique       : true,
            checkConstraint: $this->checkConstraint
        );
    }

    public function withDefault(mixed $value) : self
    {
        return new self(
            name           : $this->name,
            type           : $this->type,
            nullability    : FieldNullability::NULLABLE,
            generatedType  : $this->generatedType,
            length         : $this->length,
            precision      : $this->precision,
            scale          : $this->scale,
            defaultValue   : $value,
            isPrimaryKey   : $this->isPrimaryKey,
            isUnique       : $this->isUnique,
            checkConstraint: $this->checkConstraint
        );
    }

    public function autoIncrement() : self
    {
        if (! in_array($this->type, [StoredFieldType::INTEGER, StoredFieldType::BIGINT], true)) {
            throw new InvalidArgumentException(
                message: sprintf('Auto-increment is only supported for INTEGER and BIGINT fields, got %s.', $this->type->value)
            );
        }

        return new self(
            name           : $this->name,
            type           : $this->type,
            nullability    : FieldNullability::NOT_NULLABLE,
            generatedType  : FieldGeneratedType::AUTO_INCREMENT,
            length         : $this->length,
            precision      : $this->precision,
            scale          : $this->scale,
            defaultValue   : null,
            isPrimaryKey   : $this->isPrimaryKey,
            isUnique       : $this->isUnique,
            checkConstraint: $this->checkConstraint
        );
    }

    public function toColumnDefinition() : string
    {
        $definition = sprintf('%s %s', $this->name, $this->type->value);

        if ($this->length !== null) {
            $definition .= sprintf('(%d)', $this->length);
        } elseif ($this->precision !== null && $this->scale !== null) {
            $definition .= sprintf('(%d,%d)', $this->precision, $this->scale);
        }

        if ($this->nullability === FieldNullability::NOT_NULLABLE) {
            $definition .= ' NOT NULL';
        }

        if ($this->generatedType !== FieldGeneratedType::NONE) {
            $definition .= ' AUTO_INCREMENT';
        }

        if ($this->defaultValue !== null) {
            $definition .= sprintf(' DEFAULT %s', $this->formatDefault(value: $this->defaultValue));
        }

        return $definition;
    }

    private function formatDefault(mixed $value) : string
    {
        return match (true) {
            $value === null   => 'NULL',
            is_bool($value)   => $value ? 'TRUE' : 'FALSE',
            is_int($value)    => (string) $value,
            is_float($value)  => (string) $value,
            is_string($value) => sprintf("'%s'", addslashes($value)),
            default           => sprintf("'%s'", addslashes(json_encode($value))),
        };
    }
}