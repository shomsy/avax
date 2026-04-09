<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Headers;

/**
 * Reads a bearer token from generic HTTP header inputs.
 */
final class ReadBearerToken
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     */
    public function execute(array $headers = [], array $server = []) : string|null
    {
        $authorization = $this->readValue($headers, 'Authorization')
            ?? $this->readValue($server, 'HTTP_AUTHORIZATION')
            ?? $this->readValue($server, 'REDIRECT_HTTP_AUTHORIZATION');

        if ($authorization === null) {
            return null;
        }

        if (! preg_match('/^\s*Bearer\s+(.+)\s*$/i', $authorization, $matches)) {
            return null;
        }

        $token = trim($matches[1]);

        return $token === '' ? null : $token;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function readValue(array $values, string $key) : string|null
    {
        foreach ($values as $candidateKey => $value) {
            if (! is_string($candidateKey) || strcasecmp($candidateKey, $key) !== 0) {
                continue;
            }

            if (is_array($value)) {
                $value = reset($value);
            }

            return is_scalar($value) ? (string) $value : null;
        }

        return null;
    }
}
