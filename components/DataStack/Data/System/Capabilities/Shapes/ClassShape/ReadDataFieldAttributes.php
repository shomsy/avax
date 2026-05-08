<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape;

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
    public function read(?ReflectionProperty $reflectionProperty = null, ?ReflectionParameter $reflectionParameter = null): array
    {
        $instances = [];

        foreach ([...($reflectionParameter?->getAttributes() ?? []), ...($reflectionProperty?->getAttributes() ?? [])] as $attribute) {
            $instances[] = $attribute->newInstance();
        }

        return $instances;
    }
}
