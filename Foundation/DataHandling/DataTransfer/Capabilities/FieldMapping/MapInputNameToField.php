<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\FieldMapping;

use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;

final readonly class MapInputNameToField
{
    /**
     * @return array<string, DataField>
     */
    public function map(DataShape $shape) : array
    {
        $map = [];

        foreach ($shape->fields() as $field) {
            $map[$field->inputName] = $field;
        }

        return $map;
    }
}
