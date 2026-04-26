<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

interface ValueCasterInterface
{
    public function cast(mixed $value, DataField $field, ValueConversionContext $context) : mixed;
}
