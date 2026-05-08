<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;
use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataShape;
use Avax\Components\DataStack\Data\System\Capabilities\Shapes\Visibility\ReadVisibleFields;

final readonly class ReadVisibleDataFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $dataShape, bool $excludeHidden = true): array
    {
        return new ReadVisibleFields()->read(dataShape: $dataShape, excludeHidden: $excludeHidden);
    }
}
