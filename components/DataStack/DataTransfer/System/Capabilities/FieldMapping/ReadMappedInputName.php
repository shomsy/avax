<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\FieldMapping;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;

final readonly class ReadMappedInputName
{
    /**
     * @param object[] $attributes
     */
    public function read(string $fieldName, array $attributes, DataTransferConfig $dataTransferConfig) : FieldInputName
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof MapFrom) {
                return new FieldInputName(value: $attribute->name);
            }
        }

        return new FieldInputName(value: $dataTransferConfig->inputNameFor(fieldName: $fieldName));
    }
}
