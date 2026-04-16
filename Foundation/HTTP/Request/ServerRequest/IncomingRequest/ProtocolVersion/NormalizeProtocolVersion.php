<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion;

/**
 * Action Owner: Normalizes the HTTP protocol version.
 *
 * Extracts only the numeric portion (e.g., "1.1") from strings like "HTTP/1.1".
 * Defaults to "1.1" if not provided or invalid.
 */
final readonly class NormalizeProtocolVersion
{
    public function execute(string|null $protocol = null) : string
    {
        if ($protocol === null || $protocol === '') {
            return '1.1';
        }

        // Handle formats like "HTTP/1.1" or "1.1"
        if (preg_match('#(?:HTTP/)?(?P<version>1\.[01]|2(?:\.0)?|3)#i', $protocol, $matches)) {
            return $matches['version'];
        }

        return '1.1';
    }
}
