<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

use ReflectionParameter;
use ReflectionProperty;

/**
 * Reads attributes from properties or parameters for DTO field metadata.
 */
final readonly class ReadDataFieldAttributes
{
    /**
     * @return object[]
     */
    public function read(ReflectionProperty|null $reflectionProperty = null, ReflectionParameter|null $reflectionParameter = null) : array
    {
        $instances = [];

        foreach ([...($reflectionParameter?->getAttributes() ?? []), ...($reflectionProperty?->getAttributes() ?? [])] as $attribute) {
            $instances[] = $attribute->newInstance();
        }

        return $instances;
    }
}
