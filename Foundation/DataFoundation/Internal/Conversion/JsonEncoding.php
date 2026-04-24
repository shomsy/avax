<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Conversion;

use Avax\DataFoundation\Exceptions\EncodingException;

/**
 * Encodes arrays and public value shapes into JSON.
 */
final readonly class JsonEncoding
{
    public static function encode(mixed $value, int $flags = 0) : string
    {
        $json = json_encode($value, $flags);

        if ($json === false) {
            throw EncodingException::jsonEncodingFailed(error: json_last_error_msg());
        }

        return $json;
    }
}
