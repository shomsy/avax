<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\FieldMapping;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;
use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataShape;

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
