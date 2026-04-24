<?php

declare(strict_types=1);

namespace Avax\DataLayer\ShapeStoredData;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

final readonly class StoredModel implements IteratorAggregate
{
    public string       $name;
    public string       $tableName;
    public string       $schemaName;
    public array        $fields;
    public array        $relations;
    public array        $indexes;
    public array        $constraints;
    public ?TenantShape $tenantShape;
    public ?string      $comment;
    public string       $engine;
    public ?string      $charset;
    public ?string      $collation;

    private function __construct(
        string       $name,
        string       $tableName,
        string|null $schemaName = null,
        array|null  $fields = null,
        array|null  $relations = null,
        array|null  $indexes = null,
        array|null  $constraints = null,
        ?TenantShape $tenantShape = null,
        ?string      $comment = null,
        string|null $engine = null,
        ?string      $charset = null,
        ?string      $collation = null
    )
    {
        $schemaName  ??= 'public';
        $fields      ??= [];
        $relations   ??= [];
        $indexes     ??= [];
        $constraints ??= [];
        $engine      ??= 'InnoDB';
        $this->name        = $name;
        $this->tableName   = $tableName;
        $this->schemaName  = $schemaName;
        $this->fields      = $fields;
        $this->relations   = $relations;
        $this->indexes     = $indexes;
        $this->constraints = $constraints;
        $this->tenantShape = $tenantShape;
        $this->comment     = $comment;
        $this->engine      = $engine;
        $this->charset     = $charset ?? 'utf8mb4';
        $this->collation   = $collation ?? 'utf8mb4_unicode_ci';
    }

    public static function create(
        string $name,
        string $tableName,
        string $schemaName = 'public'
    ) : self
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Model name cannot be empty.');
        }

        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            throw new InvalidArgumentException(
                sprintf('Invalid model name "%s". Must start with uppercase and contain only alphanumeric characters.', $name)
            );
        }

        if (empty(trim($tableName))) {
            throw new InvalidArgumentException('Table name cannot be empty.');
        }

        return new self(
            name      : $name,
            tableName : $tableName,
            schemaName: $schemaName
        );
    }

    public function withField(StoredField $field) : self
    {
        $fields               = $this->fields;
        $fields[$field->name] = $field;

        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $fields,
            relations  : $this->relations,
            indexes    : $this->indexes,
            constraints: $this->constraints,
            tenantShape: $this->tenantShape,
            comment    : $this->comment,
            engine     : $this->engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function withFields(StoredField ...$fields) : self
    {
        $result = $this;
        foreach ($fields as $field) {
            $result = $result->withField($field);
        }

        return $result;
    }

    public function withRelation(StoredRelation $relation) : self
    {
        $relations                  = $this->relations;
        $relations[$relation->name] = $relation;

        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $this->fields,
            relations  : $relations,
            indexes    : $this->indexes,
            constraints: $this->constraints,
            tenantShape: $this->tenantShape,
            comment    : $this->comment,
            engine     : $this->engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function withIndex(StoredIndex $index) : self
    {
        $indexes               = $this->indexes;
        $indexes[$index->name] = $index;

        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $this->fields,
            relations  : $this->relations,
            indexes    : $indexes,
            constraints: $this->constraints,
            tenantShape: $this->tenantShape,
            comment    : $this->comment,
            engine     : $this->engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function withConstraint(StoredConstraint $constraint) : self
    {
        $constraints                    = $this->constraints;
        $constraints[$constraint->name] = $constraint;

        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $this->fields,
            relations  : $this->relations,
            indexes    : $this->indexes,
            constraints: $constraints,
            tenantShape: $this->tenantShape,
            comment    : $this->comment,
            engine     : $this->engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function withTenantShape(TenantShape $tenantShape) : self
    {
        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $this->fields,
            relations  : $this->relations,
            indexes    : $this->indexes,
            constraints: $this->constraints,
            tenantShape: $tenantShape,
            comment    : $this->comment,
            engine     : $this->engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function withComment(string $comment) : self
    {
        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $this->fields,
            relations  : $this->relations,
            indexes    : $this->indexes,
            constraints: $this->constraints,
            tenantShape: $this->tenantShape,
            comment    : $comment,
            engine     : $this->engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function withEngine(string $engine) : self
    {
        return new self(
            name       : $this->name,
            tableName  : $this->tableName,
            schemaName : $this->schemaName,
            fields     : $this->fields,
            relations  : $this->relations,
            indexes    : $this->indexes,
            constraints: $this->constraints,
            tenantShape: $this->tenantShape,
            comment    : $this->comment,
            engine     : $engine,
            charset    : $this->charset,
            collation  : $this->collation
        );
    }

    public function getField(string $name) : ?StoredField
    {
        return $this->fields[$name] ?? null;
    }

    public function getPrimaryKeyFields() : array
    {
        return array_filter(
            $this->fields,
            fn (StoredField $field) => $field->isPrimaryKey
        );
    }

    public function getRelation(string $name) : ?StoredRelation
    {
        return $this->relations[$name] ?? null;
    }

    public function getIndex(string $name) : ?StoredIndex
    {
        return $this->indexes[$name] ?? null;
    }

    public function getConstraint(string $name) : ?StoredConstraint
    {
        return $this->constraints[$name] ?? null;
    }

    public function hasPrimaryKey() : bool
    {
        return ! empty($this->getPrimaryKeyFields());
    }

    public function toTableName() : string
    {
        return sprintf('%s.%s', $this->schemaName, $this->tableName);
    }

    public function toCreateTableSQL() : string
    {
        $columns = [];
        foreach ($this->fields as $field) {
            $columns[] = $field->toColumnDefinition();
        }

        $primaryKeys = $this->getPrimaryKeyFields();
        if (! empty($primaryKeys)) {
            $pkNames   = array_map(fn (StoredField $f) => $f->name, $primaryKeys);
            $columns[] = sprintf('PRIMARY KEY (%s)', implode(', ', $pkNames));
        }

        $uniques = array_filter($this->fields, fn (StoredField $f) => $f->isUnique && ! $f->isPrimaryKey);
        foreach ($uniques as $field) {
            $columns[] = sprintf('UNIQUE KEY %s (%s)', $field->name, $field->name);
        }

        $sql = sprintf(
            "CREATE TABLE %s (\n  %s\n) ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s",
            $this->toTableName(),
            implode(",\n  ", $columns),
            $this->engine,
            $this->charset,
            $this->collation
        );

        if ($this->comment !== null) {
            $sql .= sprintf(" COMMENT='%s'", addslashes($this->comment));
        }

        return $sql;
    }

    public function getIterator() : Traversable
    {
        foreach ($this->fields as $name => $field) {
            yield $name => $field;
        }
    }
}