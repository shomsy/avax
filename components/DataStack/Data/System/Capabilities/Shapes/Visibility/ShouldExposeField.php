<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Shapes\Visibility;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;

final readonly class ShouldExposeField
{
    public function check(DataField $dataField, bool $excludeHidden = true): bool
    {
        if (! $excludeHidden) {
            return true;
        }

        return ! new HideFieldFromOutput()->shouldHide(dataField: $dataField);
    }
}
