<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\FieldMapping;

use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\Attributes\MapFrom;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;

final readonly class ReadMappedInputName
{
    /**
     * @param object[] $attributes
     */
    public function read(string $fieldName, array $attributes, DataTransferConfig $dataTransferConfig): FieldInputName
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof MapFrom) {
                return new FieldInputName(value: $attribute->name);
            }
        }

        return new FieldInputName(value: $dataTransferConfig->inputNameFor(fieldName: $fieldName));
    }
}
