<?php

declare(strict_types=1);

namespace components\DataFoundation\Interop\Json;

use components\DataFoundation\Internal\Conversion\JsonEncoding;
use components\DataFoundation\Interop\Arrays\ToArray;

/**
 * JSON encoding boundary.
 */
final readonly class ToJson
{
    public static function from(mixed $value, int $flags = 0) : string
    {
        return JsonEncoding::encode(value: ToArray::from(value: $value), flags: $flags);
    }
}
