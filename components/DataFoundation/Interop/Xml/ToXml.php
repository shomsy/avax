<?php

declare(strict_types=1);

namespace components\DataFoundation\Interop\Xml;

use components\DataFoundation\Internal\Conversion\XmlEncoding;
use components\DataFoundation\Interop\Arrays\ToArray;

/**
 * XML encoding boundary.
 */
final readonly class ToXml
{
    public static function from(mixed $value, string $rootElement = 'root') : string
    {
        return XmlEncoding::encode(value: ToArray::from(value: $value), rootElement: $rootElement);
    }
}
