<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldVisibility;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataShape;

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
