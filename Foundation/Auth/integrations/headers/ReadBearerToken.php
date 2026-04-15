<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Headers;

use SensitiveParameter;

/**
 * Reads a bearer token from generic HTTP header inputs.
 */
final class ReadBearerToken
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     */
    public function execute(#[SensitiveParameter] array|null $headers = null, array $server = []) : string|null
    {
        $headers    ??= [];
        $candidates = [
            $this->readValue(values: $headers, key: 'Authorization'),
            $this->readValue(values: $server, key: 'HTTP_AUTHORIZATION'),
            $this->readValue(values: $server, key: 'REDIRECT_HTTP_AUTHORIZATION'),
        ];

        foreach ($candidates as $authorization) {
            if ($authorization === null) {
                continue;
            }

            if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $authorization, $matches) !== 1) {
                continue;
            }

            $token = trim($matches[1]);

            if ($token !== '') {
                return $token;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function readValue(array $values, string $key) : string|null
    {
        foreach ($values as $candidateKey => $value) {
            if (strcasecmp($candidateKey, $key) !== 0) {
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
