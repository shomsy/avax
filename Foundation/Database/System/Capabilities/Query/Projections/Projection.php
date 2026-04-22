<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Projections;

interface Projection
{
    public function getTargetClass() : string;

    public function getFieldMappings() : array;

    public function map(array $row) : object;
}
