<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Shapes\Visibility;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $dataField): bool
    {
        return $dataField->isHidden();
    }
}
