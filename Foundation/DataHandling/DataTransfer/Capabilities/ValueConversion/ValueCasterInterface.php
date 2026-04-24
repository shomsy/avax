<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ValueConversion;

use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;

interface ValueCasterInterface
{
    public function cast(mixed $value, DataField $field, ValueConversionContext $context) : mixed;
}
