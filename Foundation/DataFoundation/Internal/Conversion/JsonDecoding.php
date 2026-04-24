<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Conversion;

use Avax\DataFoundation\Exceptions\EncodingException;

/**
 * Decodes JSON into array-backed values.
 */
final readonly class JsonDecoding
{
    public static function decode(string $json, bool $associative = true) : mixed
    {
        $value = json_decode($json, $associative);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw EncodingException::jsonDecodingFailed(error: json_last_error_msg());
        }

        return $value;
    }
}
