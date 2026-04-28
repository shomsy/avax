<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\Projections;

final class ProjectionBuilder implements Projection
{
    /** @var array<string, string> */
    private array $fieldMappings = [];

    public function __construct(private readonly string $targetClass) {}

    public static function for(string $targetClass) : self
    {
        return new self(targetClass: $targetClass);
    }

    public function mapColumn(string $column, string $property) : self
    {
        $this->fieldMappings[$column] = $property;

        return $this;
    }

    public function getTargetClass() : string
    {
        return $this->targetClass;
    }

    public function getFieldMappings() : array
    {
        if ($this->fieldMappings !== []) {
            return $this->fieldMappings;
        }

        return new ResultMapper(className: $this->targetClass)->getFieldMappings();
    }

    public function map(array $row) : object
    {
        if ($this->fieldMappings === []) {
            return new ResultMapper(className: $this->targetClass)->map(row: $row);
        }

        $normalized = [];
        foreach ($this->fieldMappings as $column => $property) {
            if (array_key_exists(key: $column, array: $row)) {
                $normalized[$property] = $row[$column];
            }
        }

        return new ResultMapper(className: $this->targetClass)->map(row: $normalized);
    }
}
