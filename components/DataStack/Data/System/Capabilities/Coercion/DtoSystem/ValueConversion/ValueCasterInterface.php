<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\ValueConversion;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\DataField;

interface ValueCasterInterface
{
    public function cast(mixed $value, DataField $dataField, ValueConversionContext $valueConversionContext): mixed;
}
