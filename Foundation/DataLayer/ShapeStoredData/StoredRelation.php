<?php

declare(strict_types=1);

namespace Avax\DataLayer\ShapeStoredData;

use InvalidArgumentException;

enum RelationType: string
{
    case ONE_TO_ONE   = 'one_to_one';
    case ONE_TO_MANY  = 'one_to_many';
    case MANY_TO_ONE  = 'many_to_one';
    case MANY_TO_MANY = 'many_to_many';
}

enum OnDeleteAction: string
{
    case CASCADE     = 'CASCADE';
    case SET_NULL    = 'SET NULL';
    case RESTRICT    = 'RESTRICT';
    case NO_ACTION   = 'NO ACTION';
    case SET_DEFAULT = 'SET DEFAULT';
}

enum OnUpdateAction: string
{
    case CASCADE     = 'CASCADE';
    case SET_NULL    = 'SET NULL';
    case RESTRICT    = 'RESTRICT';
    case NO_ACTION   = 'NO ACTION';
    case SET_DEFAULT = 'SET DEFAULT';
}

final readonly class StoredRelation
{
    public string         $name;
    public RelationType   $type;
    public string         $sourceModel;
    public string         $sourceField;
    public string         $targetModel;
    public string         $targetField;
    public OnDeleteAction $onDelete;
    public OnUpdateAction $onUpdate;
    public bool           $isRequired;
    public ?string        $junctionTable;
    public ?string        $junctionSourceField;
    public ?string        $junctionTargetField;

    private function __construct(
        string         $name,
        RelationType   $type,
        string         $sourceModel,
        string         $sourceField,
        string         $targetModel,
        string         $targetField,
        OnDeleteAction $onDelete = OnDeleteAction::RESTRICT,
        OnUpdateAction $onUpdate = OnUpdateAction::RESTRICT,
        bool           $isRequired = false,
        ?string        $junctionTable = null,
        ?string        $junctionSourceField = null,
        ?string        $junctionTargetField = null
    )
    {
        $this->name                = $name;
        $this->type                = $type;
        $this->sourceModel         = $sourceModel;
        $this->sourceField         = $sourceField;
        $this->targetModel         = $targetModel;
        $this->targetField         = $targetField;
        $this->onDelete            = $onDelete;
        $this->onUpdate            = $onUpdate;
        $this->isRequired          = $isRequired;
        $this->junctionTable       = $junctionTable;
        $this->junctionSourceField = $junctionSourceField;
        $this->junctionTargetField = $junctionTargetField;
    }

    public static function create(
        string       $name,
        RelationType $type,
        string       $sourceModel,
        string       $sourceField,
        string       $targetModel,
        string       $targetField,
        array        $options = []
    ) : self
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Relation name cannot be empty.');
        }

        if (empty(trim($sourceModel))) {
            throw new InvalidArgumentException('Source model cannot be empty.');
        }

        if (empty(trim($targetModel))) {
            throw new InvalidArgumentException('Target model cannot be empty.');
        }

        if ($type === RelationType::MANY_TO_MANY && empty($options['junction_table'])) {
            throw new InvalidArgumentException('Many-to-many relations require a junction table.');
        }

        return new self(
            name               : $name,
            type               : $type,
            sourceModel        : $sourceModel,
            sourceField        : $sourceField,
            targetModel        : $targetModel,
            targetField        : $targetField,
            onDelete           : OnDeleteAction::from($options['on_delete'] ?? 'RESTRICT'),
            onUpdate           : OnUpdateAction::from($options['on_update'] ?? 'RESTRICT'),
            isRequired         : $options['required'] ?? false,
            junctionTable      : $options['junction_table'] ?? null,
            junctionSourceField: $options['junction_source_field'] ?? null,
            junctionTargetField: $options['junction_target_field'] ?? null
        );
    }

    public static function belongsTo(
        string $name,
        string $sourceModel,
        string $sourceField,
        string $targetModel,
        string $targetField = 'id',
        array  $options = []
    ) : self
    {
        return self::create(
            name       : $name,
            type       : RelationType::MANY_TO_ONE,
            sourceModel: $sourceModel,
            sourceField: $sourceField,
            targetModel: $targetModel,
            targetField: $targetField,
            options    : $options
        );
    }

    public static function hasOne(
        string $name,
        string $sourceModel,
        string $sourceField,
        string $targetModel,
        string $targetField,
        array  $options = []
    ) : self
    {
        return self::create(
            name       : $name,
            type       : RelationType::ONE_TO_ONE,
            sourceModel: $sourceModel,
            sourceField: $sourceField,
            targetModel: $targetModel,
            targetField: $targetField,
            options    : $options
        );
    }

    public static function hasMany(
        string $name,
        string $sourceModel,
        string $sourceField,
        string $targetModel,
        string $targetField,
        array  $options = []
    ) : self
    {
        return self::create(
            name       : $name,
            type       : RelationType::ONE_TO_MANY,
            sourceModel: $sourceModel,
            sourceField: $sourceField,
            targetModel: $targetModel,
            targetField: $targetField,
            options    : $options
        );
    }

    public static function belongsToMany(
        string $name,
        string $sourceModel,
        string $targetModel,
        string $junctionTable,
        string $junctionSourceField,
        string $junctionTargetField,
        array  $options = []
    ) : self
    {
        return self::create(
            name       : $name,
            type       : RelationType::MANY_TO_MANY,
            sourceModel: $sourceModel,
            sourceField: $junctionSourceField,
            targetModel: $targetModel,
            targetField: $junctionTargetField,
            options    : array_merge($options, [
                             'junction_table'        => $junctionTable,
                             'junction_source_field' => $junctionSourceField,
                             'junction_target_field' => $junctionTargetField,
                         ])
        );
    }

    public function toForeignKeySQL() : string
    {
        $sql = sprintf(
            'FOREIGN KEY (%s) REFERENCES %s(%s)',
            $this->sourceField,
            $this->targetModel,
            $this->targetField
        );

        if ($this->onDelete !== OnUpdateAction::RESTRICT) {
            $sql .= sprintf(' ON DELETE %s', $this->onDelete->value);
        }

        if ($this->onUpdate !== OnUpdateAction::RESTRICT) {
            $sql .= sprintf(' ON UPDATE %s', $this->onUpdate->value);
        }

        return $sql;
    }

    public function toJoinSQL() : string
    {
        return match ($this->type) {
            RelationType::MANY_TO_ONE  => sprintf(
                'LEFT JOIN %s ON %s.%s = %s.%s',
                $this->targetModel,
                $this->sourceModel,
                $this->sourceField,
                $this->targetModel,
                $this->targetField
            ),
            RelationType::ONE_TO_ONE   => sprintf(
                'LEFT JOIN %s ON %s.%s = %s.%s',
                $this->targetModel,
                $this->sourceModel,
                $this->sourceField,
                $this->targetModel,
                $this->targetField
            ),
            RelationType::ONE_TO_MANY  => sprintf(
                'LEFT JOIN %s ON %s.%s = %s.%s',
                $this->targetModel,
                $this->sourceModel,
                $this->sourceField,
                $this->targetModel,
                $this->targetField
            ),
            RelationType::MANY_TO_MANY => sprintf(
                'LEFT JOIN %s ON %s.%s = %s.%s',
                $this->junctionTable,
                $this->sourceField,
                $this->sourceModel,
                $this->sourceField
            ),
        };
    }
}