<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\FieldVisibility;

use Avax\Components\Data\System\Capabilities\DataShape\DataField;

final readonly class ShouldExposeField
{
    public function check(DataField $field, bool $excludeHidden = true) : bool
    {
        if (! $excludeHidden) {
            return true;
        }

        return ! new HideFieldFromOutput()->shouldHide(field: $field);
    }
}
