<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataShape;

use ReflectionAttribute;
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
            if ($attribute instanceof ReflectionAttribute) {
                $instances[] = $attribute->newInstance();
            }
        }

        return $instances;
    }
}
