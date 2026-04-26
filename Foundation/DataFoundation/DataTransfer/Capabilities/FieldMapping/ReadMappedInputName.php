<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldMapping;

use Avax\DataFoundation\DataTransfer\Capabilities\Attributes\MapFrom;
use Avax\DataFoundation\DataTransfer\Configuration\DataTransferConfig;

final readonly class ReadMappedInputName
{
    /**
     * @param object[] $attributes
     */
    public function read(string $fieldName, array $attributes, DataTransferConfig $config) : FieldInputName
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof MapFrom) {
                return new FieldInputName(value: $attribute->name);
            }
        }

        return new FieldInputName(value: $config->inputNameFor(fieldName: $fieldName));
    }
}
