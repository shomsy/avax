<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\ReadDataObject;

use Avax\Components\Data\System\Capabilities\DataShape\DataField;
use Avax\Components\Data\System\Capabilities\DataShape\DataShape;
use Avax\Components\Data\System\Capabilities\FieldVisibility\ReadVisibleFields;

final readonly class ReadVisibleDataFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $shape, bool $excludeHidden = true) : array
    {
        return new ReadVisibleFields()->read(shape: $shape, excludeHidden: $excludeHidden);
    }
}
