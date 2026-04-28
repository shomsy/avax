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
    public function read(ReflectionProperty|null $property = null, ReflectionParameter|null $parameter = null) : array
    {
        $instances = [];

        foreach ([...($parameter?->getAttributes() ?? []), ...($property?->getAttributes() ?? [])] as $attribute) {
            if ($attribute instanceof ReflectionAttribute) {
                $instances[] = $attribute->newInstance();
            }
        }

        return $instances;
    }
}
