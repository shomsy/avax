<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\FieldVisibility;

use Avax\Components\Data\System\Capabilities\DataShape\DataField;
use Avax\Components\Data\System\Capabilities\DataShape\DataShape;

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
