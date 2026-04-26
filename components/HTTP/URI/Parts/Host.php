<?php

declare(strict_types=1);

namespace components\HTTP\URI\Parts;

use InvalidArgumentException;
use Stringable;

/**
 * Represents a URI host.
 */
final readonly class Host implements Stringable
{
    private string $host;

    public function __construct(string $host)
    {
        $this->host = $this->validate(host: $host);
    }

    private function validate(string $host) : string
    {
        $normalized = trim($host);
        if ($normalized === '') {
            throw new InvalidArgumentException(message: 'Host cannot be empty.');
        }

        if (str_contains($normalized, ':') && ! str_starts_with($normalized, '[')) {
            $parsed = parse_url('https://' . $normalized);
            if ($parsed !== false && isset($parsed['host'])) {
                $normalized = $parsed['host'];
            }
        }

        $ascii = $normalized;
        if (function_exists('idn_to_ascii')) {
            $flags     = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
            $variant   = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
            $converted = idn_to_ascii($normalized, $flags, $variant);
            if ($converted !== false) {
                $ascii = $converted;
            }
        }

        if (! filter_var($ascii, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && ! filter_var($ascii, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException(message: 'Invalid host: ' . $normalized);
        }

        return strtolower($ascii);
    }

    public function __toString() : string
    {
        return $this->host;
    }
}