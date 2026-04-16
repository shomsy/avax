<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerEnvironment;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * State Owner: Structured Data Transfer Object for Server Parameters ($_SERVER).
 * Provides a strongly typed, predictable interface over raw superglobal arrays.
 */
final class ServerEnvironment extends AbstractDTO
{
    public string|null $documentRoot = null;
    public string|null $remoteAddr = null;
    public string|null $serverSoftware = null;
    public string|null $serverProtocol = null;
    public string|null $serverName = null;
    public string|null $serverAddr = null;
    public string|null $serverPort = null;
    public string|null $requestMethod = null;
    public string|null $requestUri = null;
    public string|null $queryString = null;
    public string|null $https = null;

    // HTTP prefixed values
    public string|null $httpHost = null;
    public string|null $httpUserAgent = null;
    public string|null $httpAccept = null;
    public string|null $httpAcceptEncoding = null;
    public string|null $httpAcceptLanguage = null;
    public string|null $httpConnection = null;
    public string|null $httpReferer = null;

    // Timestamps
    public int $requestTime = 0;
    public float $requestTimeFloat = 0.0;

    /**
     * Create a DTO instance from raw PSR-7 server parameters.
     * Maps ALL_CAPS_SNAKE_CASE keys to camelCase properties.
     *
     * @param array<string, mixed> $serverParams
     */
    public static function fromServerParams(array $serverParams): self
    {
        $normalized = [];
        foreach ($serverParams as $key => $value) {
            $normalized[camel(value: $key)] = $value;
        }

        return new self(data: $normalized);
    }

    /**
     * Helper to check if connection is secure.
     */
    public function isHttps(): bool
    {
        if ($this->https !== null && strtolower($this->https) !== 'off') {
            return true;
        }

        return false;
    }

    /**
     * Helper to check if request is from localhost.
     */
    public function isLocal(): bool
    {
        return in_array(
            needle: $this->remoteAddr,
            haystack: ['127.0.0.1', '::1'],
            strict: true
        );
    }
}
