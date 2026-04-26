<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldVisibility;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $field) : bool
    {
        return $field->isHidden();
    }
}
