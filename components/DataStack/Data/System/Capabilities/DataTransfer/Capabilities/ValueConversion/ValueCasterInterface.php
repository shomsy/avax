<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ValueConversion;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;

interface ValueCasterInterface
{
    public function cast(mixed $value, DataField $dataField, ValueConversionContext $valueConversionContext): mixed;
}
