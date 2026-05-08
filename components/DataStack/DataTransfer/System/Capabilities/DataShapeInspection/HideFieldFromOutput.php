<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

final readonly class HideFieldFromOutput
{
    public function shouldHide(DataField $dataField) : bool
    {
        return $dataField->isHidden();
    }
}
