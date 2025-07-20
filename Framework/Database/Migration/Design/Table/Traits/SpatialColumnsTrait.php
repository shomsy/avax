<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Table\Traits;

use Gemini\Database\Migration\Design\Column\ColumnBuilder;

trait SpatialColumnsTrait
{
    public function geometry(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'GEOMETRY', name: $name);
    }

    public function point(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'POINT', name: $name);
    }

    public function lineString(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'LINESTRING', name: $name);
    }

    public function polygon(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'POLYGON', name: $name);
    }

    public function multiPoint(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'MULTIPOINT', name: $name);
    }

    public function multiLineString(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'MULTILINESTRING', name: $name);
    }

    public function multiPolygon(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'MULTIPOLYGON', name: $name);
    }

    public function geometryCollection(string $name) : ColumnBuilder
    {
        return $this->addColumn(type: 'GEOMETRYCOLLECTION', name: $name);
    }
}