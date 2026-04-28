<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\FieldVisibility;

use Avax\Components\Data\System\Capabilities\DataShape\DataField;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $field) : bool
    {
        return $field->isHidden();
    }
}
