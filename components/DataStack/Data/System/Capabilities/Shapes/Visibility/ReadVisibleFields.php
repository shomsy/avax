<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Shapes\Visibility;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;
use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataShape;

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
                dataField    : $dataField,
            ),
        );
    }
}
