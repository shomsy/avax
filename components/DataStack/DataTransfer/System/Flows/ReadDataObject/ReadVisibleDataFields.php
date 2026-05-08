<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\ReadDataObject;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\ReadVisibleFields;

final readonly class ReadVisibleDataFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $dataShape, bool $excludeHidden = true) : array
    {
        return new ReadVisibleFields()->read(dataShape: $dataShape, excludeHidden: $excludeHidden);
    }
}
