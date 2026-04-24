<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\ReadDataObject;

use Avax\DataHandling\DataTransfer\Capabilities\FieldVisibility\ReadVisibleFields;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;

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
