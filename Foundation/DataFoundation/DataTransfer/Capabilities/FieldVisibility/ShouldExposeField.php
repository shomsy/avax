<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldVisibility;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

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
