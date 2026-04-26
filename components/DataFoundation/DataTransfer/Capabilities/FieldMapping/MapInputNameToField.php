<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\Capabilities\FieldMapping;

use components\DataFoundation\DataTransfer\InspectDataShape\DataField;
use components\DataFoundation\DataTransfer\InspectDataShape\DataShape;

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
