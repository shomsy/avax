<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities;

/**
 * Canonical HTTP protocol versions as a backed enum.
 *
 * Replaces bare string literals like '1.0', '1.1', '2' with type-safe enum cases.
 */
enum ProtocolVersion: string
{
    case Http10 = '1.0';
    case Http11 = '1.1';
    case Http2 = '2';
    case Http3 = '3';

    /**
     * Create a ProtocolVersion from a string value.
     */
    public static function fromString(string $version): self
    {
        return match ($version) {
            '1.0' => self::Http10,
            '1.1' => self::Http11,
            '2', '2.0' => self::Http2,
            '3', '3.0' => self::Http3,
            default => self::Http11,
        };
    }
}
