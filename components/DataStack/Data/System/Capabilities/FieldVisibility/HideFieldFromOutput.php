<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\FieldVisibility;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $field) : bool
    {
        return $field->isHidden();
    }
}
