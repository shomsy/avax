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
    public function __construct(
        private array $sensitiveKeys = [
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
        ]
    ) {}

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function execute(array $context) : array
    {
        $masked = [];

        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $masked[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $masked[$key] = $this->maskArray($value);
                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private function maskArray(array $value) : array
    {
        $masked = [];

        foreach ($value as $itemKey => $itemValue) {
            if (is_string($itemKey) && $this->isSensitiveKey($itemKey)) {
                $masked[$itemKey] = '[redacted]';
                continue;
            }

            if (is_array($itemValue)) {
                $masked[$itemKey] = $this->maskArray($itemValue);
                continue;
            }

            $masked[$itemKey] = $itemValue;
        }

        return $masked;
    }

    private function isSensitiveKey(string $key) : bool
    {
        $normalized = strtolower(trim($key));

        foreach ($this->sensitiveKeys as $candidate) {
            if ($normalized === strtolower($candidate)) {
                return true;
            }
        }

        return false;
    }
}
