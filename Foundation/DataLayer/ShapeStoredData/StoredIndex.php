<?php

declare(strict_types=1);

namespace Avax\DataLayer\ShapeStoredData;

use InvalidArgumentException;

enum IndexType: string
{
    case BTREE = 'BTREE';
    case HASH  = 'HASH';
    case GIN   = 'GIN';
    case GIST  = 'GIST';
    case BRIN  = 'BRIN';
}

enum IndexKind: string
{
    case PRIMARY  = 'primary';
    case UNIQUE   = 'unique';
    case FULLTEXT = 'fulltext';
    case SPATIAL  = 'spatial';
    case INDEX    = 'index';
}

final readonly class StoredIndex
{
    public string    $name;
    public IndexKind $kind;
    public IndexType $type;
    public string    $tableName;
    public array     $columns;
    public ?int      $length;
    public bool      $isVisible;
    public bool      $isPartial;
    public ?string   $partialExpression;
    public ?string   $comment;
    public ?int      $priority;

    private function __construct(
        string    $name,
        IndexKind $kind,
        IndexType $type,
        string    $tableName,
        array     $columns,
        ?int      $length = null,
        bool      $isVisible = true,
        bool      $isPartial = false,
        ?string   $partialExpression = null,
        ?string   $comment = null,
        ?int      $priority = null
    )
    {
        $this->name              = $name;
        $this->kind              = $kind;
        $this->type              = $type;
        $this->tableName         = $tableName;
        $this->columns           = $columns;
        $this->length            = $length;
        $this->isVisible         = $isVisible;
        $this->isPartial         = $isPartial;
        $this->partialExpression = $partialExpression;
        $this->comment           = $comment;
        $this->priority          = $priority;
    }

    public static function create(
        string $name,
        string $tableName,
        array  $columns,
        array  $options = []
    ) : self
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Index name cannot be empty.');
        }

        if (empty($columns)) {
            throw new InvalidArgumentException('Index must have at least one column.');
        }

        if (! is_array($columns)) {
            throw new InvalidArgumentException('Columns must be an array.');
        }

        foreach ($columns as $column) {
            if (! is_string($column) || empty(trim($column))) {
                throw new InvalidArgumentException('All column names must be non-empty strings.');
            }
        }

        return new self(
            name             : $name,
            kind             : IndexKind::from($options['kind'] ?? 'index'),
            type             : IndexType::from($options['type'] ?? 'BTREE'),
            tableName        : $tableName,
            columns          : $columns,
            length           : $options['length'] ?? null,
            isVisible        : $options['visible'] ?? true,
            isPartial        : $options['partial'] ?? false,
            partialExpression: $options['where'] ?? null,
            comment          : $options['comment'] ?? null,
            priority         : $options['priority'] ?? null
        );
    }

    public static function primary(
        string $tableName,
        array  $columns,
        array  $options = []
    ) : self
    {
        return self::create(
            name     : 'PRIMARY',
            tableName: $tableName,
            columns  : $columns,
            options  : array_merge($options, ['kind' => 'primary'])
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
            tableName: $tableName,
            columns  : $columns,
            options  : array_merge($options, ['kind' => 'unique'])
        );
    }

    public static function fulltext(
        string $name,
        string $tableName,
        array  $columns,
        array  $options = []
    ) : self
    {
        if (! in_array($options['kind'] ?? '', ['fulltext', 'spatial'], true)) {
            throw new InvalidArgumentException('Full-text index requires specific database engine support.');
        }

        return self::create(
            name     : $name,
            tableName: $tableName,
            columns  : $columns,
            options  : array_merge($options, ['kind' => 'fulltext'])
        );
    }

    public static function composite(
        string $name,
        string $tableName,
        array  $columns,
        array  $options = []
    ) : self
    {
        if (count($columns) < 2) {
            throw new InvalidArgumentException('Composite index requires at least two columns.');
        }

        return self::create(
            name     : $name,
            tableName: $tableName,
            columns  : $columns,
                       $options
        );
    }

    public function toIndexSQL() : string
    {
        $kind = match ($this->kind) {
            IndexKind::PRIMARY  => 'PRIMARY KEY',
            IndexKind::UNIQUE   => sprintf('UNIQUE %s', $this->type->value),
            IndexKind::FULLTEXT => 'FULLTEXT',
            IndexKind::SPATIAL  => 'SPATIAL',
            default             => $this->type->value,
        };

        $columns = [];
        foreach ($this->columns as $column) {
            $colDef = $column;
            if ($this->length !== null) {
                $colDef .= sprintf('(%d)', $this->length);
            }
            $columns[] = $colDef;
        }

        $sql = sprintf(
            '%s %s (%s)',
            $kind,
            $this->name === 'PRIMARY' ? '' : $this->name,
            implode(', ', $columns)
        );

        if ($this->isPartial && $this->partialExpression !== null) {
            $sql .= sprintf(' WHERE %s', $this->partialExpression);
        }

        if ($this->comment !== null) {
            $sql .= sprintf(' COMMENT "%s"', addslashes($this->comment));
        }

        return $sql;
    }

    public function toCreateIndexSQL() : string
    {
        $kind = match ($this->kind) {
            IndexKind::PRIMARY  => '',
            IndexKind::UNIQUE   => 'UNIQUE',
            IndexKind::FULLTEXT => 'FULLTEXT',
            IndexKind::SPATIAL  => 'SPATIAL',
            default             => 'INDEX',
        };

        $columns = [];
        foreach ($this->columns as $column) {
            $colDef = $column;
            if ($this->length !== null) {
                $colDef .= sprintf('(%d)', $this->length);
            }
            $columns[] = $colDef;
        }

        $sql = sprintf(
            'CREATE %s INDEX %s ON %s (%s)',
            $kind,
            $this->name,
            $this->tableName,
            implode(', ', $columns)
        );

        if ($this->type !== IndexType::BTREE && $this->kind !== IndexKind::PRIMARY) {
            $sql .= sprintf(' USING %s', $this->type->value);
        }

        if ($this->isPartial && $this->partialExpression !== null) {
            $sql .= sprintf(' WHERE %s', $this->partialExpression);
        }

        return $sql;
    }

    public function toDropIndexSQL() : string
    {
        return sprintf(
            'DROP INDEX %s ON %s',
            $this->name === 'PRIMARY' ? 'PRIMARY KEY' : $this->name,
            $this->tableName
        );
    }
}