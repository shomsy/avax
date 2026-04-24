<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Xml;

use Avax\DataFoundation\Internal\Conversion\XmlEncoding;
use Avax\DataFoundation\Interop\Arrays\ToArray;

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
