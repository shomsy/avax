<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

final readonly class ShouldExposeField
{
    public function check(DataField $dataField, bool $excludeHidden = true) : bool
    {
        if (! $excludeHidden) {
            return true;
        }

        return ! new HideFieldFromOutput()->shouldHide(dataField: $dataField);
    }
}
