<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;
use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataShape;
use Avax\Components\DataStack\Data\System\Capabilities\FieldVisibility\ReadVisibleFields;

final readonly class ReadVisibleDataFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $dataShape, bool $excludeHidden = true) : array
    {
        return new ReadVisibleFields()->read(excludeHidden: $excludeHidden, shape: $dataShape);
    }
}
