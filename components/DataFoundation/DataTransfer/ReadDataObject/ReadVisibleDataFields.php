<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\ReadDataObject;

use Avax\DataFoundation\DataTransfer\Capabilities\FieldVisibility\ReadVisibleFields;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataShape;

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
