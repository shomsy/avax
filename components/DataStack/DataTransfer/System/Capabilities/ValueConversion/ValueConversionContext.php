<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\ValueConversion;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataField;

final readonly class ValueConversionContext
{
    public function __construct(
        public DataField $dataField,
        public object|null $parent = null,
        public string|null $propertyName = null,
    ) {}
}
