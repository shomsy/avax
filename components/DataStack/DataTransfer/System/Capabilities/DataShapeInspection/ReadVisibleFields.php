<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

final readonly class ReadVisibleFields
{
    /**
     * @return array<string, DataField>
     */
    public function read(DataShape $dataShape, bool $excludeHidden = true) : array
    {
        return array_filter(
            array   : $dataShape->fields(),
            callback: static fn (DataField $dataField) : bool => new ShouldExposeField()->check(
                excludeHidden: $excludeHidden,
                dataField    : $dataField,
            ),
        );
    }
}
