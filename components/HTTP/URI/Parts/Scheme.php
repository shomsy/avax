<?php

declare(strict_types=1);

namespace components\HTTP\URI\Parts;

use InvalidArgumentException;
use Stringable;

/**
 * Represents a URI scheme (e.g., http, https).
 */
final readonly class Scheme implements Stringable
{
    private const array ALLOWED_SCHEMES = ['http', 'https', 'ftp', 'ws', 'wss', 'file', 'mailto', 'data'];

    private string $scheme;

    public function __construct(string $scheme)
    {
        $this->scheme = $this->validate(scheme: $scheme);
    }

    private function validate(string $scheme) : string
    {
        if ($scheme === '' || ! in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true)) {
            throw new InvalidArgumentException(message: 'Invalid scheme: ' . $scheme);
        }

        return strtolower($scheme);
    }

    public function __toString() : string
    {
        return $this->scheme;
    }

    public function isDefaultPort(int $port) : bool
    {
        return ($this->scheme === 'http' && $port === 80) || ($this->scheme === 'https' && $port === 443);
    }
}