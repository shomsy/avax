<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\FieldVisibility;

use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $field) : bool
    {
        return $field->isHidden();
    }
}
