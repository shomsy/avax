<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

/**
 * Enum Protocol
 * Defines supported protocols for URIs and provides methods for conversion and validation.
 */
enum Protocol: string
{
    case HTTP   = 'http';

    case HTTPS  = 'https';

    case FTP    = 'ftp';

    case WS     = 'ws';

    case WSS    = 'wss';

    case FILE   = 'file';

    case MAILTO = 'mailto';

    case DATA   = 'data';

    /**
     * Validates if the given scheme is a valid protocol.
     *
     * @param string $scheme The scheme to validate.
     *
     * @return bool True if the scheme is valid, false otherwise.
     */
    public static function isValid(string $scheme) : bool
    {
        return self::fromString($scheme) instanceof self;
    }

    /**
     * Converts a string to a Protocol enum instance, ignoring case.
     *
     * @param string $protocol The protocol string to convert.
     *
     * @return Protocol|null Returns the corresponding `Protocol` instance or null if invalid.
     */
    public static function fromString(string $protocol) : self|null
    {
        return self::tryFrom(strtolower($protocol));
    }

    /**
     * Returns the default port for the protocol, if any.
     *
     * @return int|null The default port or null if no default port is defined.
     */
    public function defaultPort() : int|null
    {
        return match ($this) {
            self::HTTP, self::WS                 => 80,
            self::HTTPS, self::WSS               => 443,
            self::FTP                            => 21,
            self::FILE, self::MAILTO, self::DATA => null,
        };
    }
}
