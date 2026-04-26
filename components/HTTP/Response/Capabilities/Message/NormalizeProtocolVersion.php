<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Message;

use InvalidArgumentException;

final class NormalizeProtocolVersion
{
    public function __invoke(string $version) : string
    {
        $normalized = trim(string: str_ireplace(search: 'HTTP/', replace: '', subject: $version));

        if ($normalized === '') {
            throw new InvalidArgumentException(message: 'HTTP protocol version cannot be empty.');
        }

        if (! preg_match(pattern: '/^\d+(?:\.\d+)?$/', subject: $normalized)) {
            throw new InvalidArgumentException(message: "Invalid HTTP protocol version [{$version}].");
        }

        return $normalized;
    }
}
