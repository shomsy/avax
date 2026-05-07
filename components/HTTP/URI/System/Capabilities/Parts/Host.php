<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Capabilities\Parts;

use InvalidArgumentException;
use Stringable;

final readonly class Host implements Stringable
{
    private string $host;

    public function __construct(string $host)
    {
        $normalized = strtolower(trim($host));

        if ($normalized === '') {
            throw new InvalidArgumentException('Host cannot be empty');
        }

        if (str_contains($normalized, '..')) {
            throw new InvalidArgumentException('Host cannot contain double dots');
        }

        if (str_starts_with($normalized, '-') || str_ends_with($normalized, '-')) {
            throw new InvalidArgumentException('Host cannot start or end with a hyphen');
        }

        if (str_contains($normalized, ' ')) {
            throw new InvalidArgumentException('Host cannot contain spaces');
        }

        // IPv6 detection (simple)
        if (str_contains($normalized, ':') && ! str_starts_with($normalized, '[')) {
            throw new InvalidArgumentException('IPv6 host must be enclosed in square brackets');
        }

        $this->host = $normalized;
    }

    public function __toString() : string
    {
        return $this->host;
    }
}
