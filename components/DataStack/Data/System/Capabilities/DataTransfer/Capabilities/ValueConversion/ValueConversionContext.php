<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ValueConversion;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\DataField;

final readonly class ValueConversionContext
{
    public function __construct(
        public DataField $dataField,
        public ?object $parent = null,
        public ?string $propertyName = null,
    ) {}
}
