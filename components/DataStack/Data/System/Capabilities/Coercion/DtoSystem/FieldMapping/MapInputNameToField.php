<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\FieldMapping;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;
use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataShape;

final readonly class MapInputNameToField
{
    /**
     * @return array<string, DataField>
     */
    public function map(DataShape $dataShape): array
    {
        $map = [];

        foreach ($dataShape->fields() as $dataField) {
            $map[$dataField->inputName] = $dataField;
        }

        return $map;
    }
}
