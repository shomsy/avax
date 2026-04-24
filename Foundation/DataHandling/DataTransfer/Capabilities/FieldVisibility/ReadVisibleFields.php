<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\FieldVisibility;

use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;

final readonly class ReadVisibleFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $shape, bool $excludeHidden = true) : array
    {
        return array_filter(
            array   : $shape->fields(),
            callback: static fn (DataField $field) : bool => new ShouldExposeField()->check(
                field        : $field,
                excludeHidden: $excludeHidden,
            ),
        );
    }
}
