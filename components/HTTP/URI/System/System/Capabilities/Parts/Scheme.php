<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\System\Capabilities\Parts;

use InvalidArgumentException;
use Stringable;

final readonly class Scheme implements Stringable
{
    private const array ALLOWED = ['http', 'https', 'ftp', 'file'];
    private string $scheme;

    public function __construct(string $scheme)
    {
        $normalized = strtolower(trim($scheme));

        if ($normalized === '') {
            throw new InvalidArgumentException('Scheme cannot be empty');
        }

        if (! in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Scheme '{$normalized}' is not allowed");
        }

        $this->scheme = $normalized;
    }

    public function isDefaultPort(int $port) : bool
    {
        return ($this->scheme === 'http' && $port === 80)
            || ($this->scheme === 'https' && $port === 443);
    }

    public function __toString() : string
    {
        return $this->scheme;
    }
}
