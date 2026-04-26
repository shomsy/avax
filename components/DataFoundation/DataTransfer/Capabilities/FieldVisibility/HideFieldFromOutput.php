<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\Capabilities\FieldVisibility;

use components\DataFoundation\DataTransfer\InspectDataShape\DataField;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $field) : bool
    {
        return $field->isHidden();
    }
}
