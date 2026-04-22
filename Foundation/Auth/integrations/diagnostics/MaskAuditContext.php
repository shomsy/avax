<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

/**
 * Removes or masks sensitive audit context keys before export.
 */
final readonly class MaskAuditContext
{
    /**
     * @param list<string> $sensitiveKeys
     */
    public function __construct(private array $sensitiveKeys
                                = [
        'email',
        'ip_address',
        'user_agent',
        'access_token',
        'refresh_token',
        'token',
        'client_secret',
        'secret',
        'authorization',
        'cookie',
        'password',
        'password_hash',
        'code',
    ])
    {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function execute(array $context) : array
    {
        $masked = [];

        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey(key: $key)) {
                $masked[$key] = '[redacted]';
                continue;
            }

            if (is_array(value: $value)) {
                $masked[$key] = $this->maskArray(value: $value);
                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }

    private function isSensitiveKey(string $key) : bool
    {
        $normalized = strtolower(string: trim(string: $key));

        return array_any(array: $this->sensitiveKeys, callback: fn ($candidate) => $normalized === strtolower(string: $candidate));
    }

    /**
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    private function maskArray(array $value) : array
    {
        $masked = [];

        foreach ($value as $itemKey => $itemValue) {
            if (is_string(value: $itemKey) && $this->isSensitiveKey(key: $itemKey)) {
                $masked[$itemKey] = '[redacted]';
                continue;
            }

            if (is_array(value: $itemValue)) {
                $masked[$itemKey] = $this->maskArray(value: $itemValue);
                continue;
            }

            $masked[$itemKey] = $itemValue;
        }

        return $masked;
    }
}
