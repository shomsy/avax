<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Configuration;

/**
 * Immutable configuration object for the Response component.
 *
 * Controls default response settings such as charset,
 * default headers, and content negotiation preferences.
 */
final readonly class ResponseConfiguration
{
    /**
     * @param string                $charset            Default character set
     * @param string                $defaultContentType Default Content-Type header
     * @param array<string, string> $defaultHeaders     Default headers for all responses
     * @param bool                  $compress           Enable response compression
     * @param string                $protocolVersion    Default HTTP protocol version
     */
    public function __construct(
        private string $charset = 'utf-8',
        private string $defaultContentType = 'text/html',
        private array  $defaultHeaders = [],
        private bool   $compress = false,
        private string $protocolVersion = '1.1',
    ) {}

    public function charset() : string
    {
        return $this->charset;
    }

    public function defaultContentType() : string
    {
        return $this->defaultContentType;
    }

    /**
     * @return array<string, string>
     */
    public function defaultHeaders() : array
    {
        return $this->defaultHeaders;
    }

    public function shouldCompress() : bool
    {
        return $this->compress;
    }

    public function protocolVersion() : string
    {
        return $this->protocolVersion;
    }

    /**
     * Create a new configuration with merged overrides.
     *
     * @param array<string, mixed> $overrides
     */
    public function with(array $overrides) : self
    {
        return new self(
            charset           : $overrides['charset'] ?? $this->charset,
            defaultContentType: $overrides['default_content_type'] ?? $this->defaultContentType,
            defaultHeaders    : $overrides['default_headers'] ?? $this->defaultHeaders,
            compress          : $overrides['compress'] ?? $this->compress,
            protocolVersion   : $overrides['protocol_version'] ?? $this->protocolVersion,
        );
    }

    /**
     * Create configuration from an array.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            charset           : $config['charset'] ?? 'utf-8',
            defaultContentType: $config['default_content_type'] ?? 'text/html',
            defaultHeaders    : $config['default_headers'] ?? [],
            compress          : $config['compress'] ?? false,
            protocolVersion   : $config['protocol_version'] ?? '1.1',
        );
    }

    /**
     * Get the full Content-Type header value including charset.
     */
    public function fullContentType(string $type = '') : string
    {
        $contentType = $type !== '' ? $type : $this->defaultContentType;

        return $contentType . '; charset=' . $this->charset;
    }
}
