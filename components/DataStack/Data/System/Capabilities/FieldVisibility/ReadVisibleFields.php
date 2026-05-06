<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\FieldVisibility;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;
use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataShape;

final readonly class ReadVisibleFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $dataShape, bool $excludeHidden = true): array
    {
        return array_filter(
            array   : $dataShape->fields(),
            callback: static fn (DataField $dataField): bool => new ShouldExposeField()->check(
                excludeHidden: $excludeHidden,
                field        : $dataField,
            ),
        );
    }
}
