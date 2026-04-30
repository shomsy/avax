<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Headers;

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
    public function execute(#[SensitiveParameter] array $headers = null, array $server = []) : string|null
    {
        $headers ??= [];
        $candidates = [
            $this->readValue(values: $headers, key: 'Authorization'),
            $this->readValue(values: $server, key: 'HTTP_AUTHORIZATION'),
            $this->readValue(values: $server, key: 'REDIRECT_HTTP_AUTHORIZATION'),
        ];

        foreach ($candidates as $authorization) {
            if ($authorization === null) {
                continue;
            }

            if (preg_match(pattern: '/^\s*Bearer\s+(.+)\s*$/i', subject: $authorization, matches: $matches) !== 1) {
                continue;
            }

            $token = trim(string: $matches[1]);

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
            if (strcasecmp(string1: $candidateKey, string2: $key) !== 0) {
                continue;
            }

            if (is_array(value: $value)) {
                $value = reset(array: $value);
            }

            return is_scalar(value: $value) ? (string) $value : null;
        }

        return null;
    }
}
