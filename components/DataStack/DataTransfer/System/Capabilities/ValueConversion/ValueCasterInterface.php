<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\ValueConversion;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;

interface ValueCasterInterface
{
    public function cast(mixed $value, DataField $dataField, ValueConversionContext $valueConversionContext) : mixed;
}
