<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\FieldMapping;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;

final readonly class MapInputNameToField
{
    /**
     * @return array<string, DataField>
     */
    public function map(DataShape $dataShape) : array
    {
        $map = [];

        foreach ($dataShape->fields() as $dataField) {
            $map[$dataField->inputName] = $dataField;
        }

        return $map;
    }
}
