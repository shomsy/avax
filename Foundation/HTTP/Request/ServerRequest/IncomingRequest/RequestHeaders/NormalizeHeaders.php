<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders;

/**
 * NormalizeHeaders - Standardizes header keys and values.
 */
final readonly class NormalizeHeaders
{
    /**
     * @param array<string, string|string[]> $headers
     * @return array<string, string[]>
     */
    public function execute(array $headers) : array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            if (! is_string($name) || $name === '') {
                continue;
            }

            $normalized[$name] = $this->normalizeValue(value: $value);
        }

        return $normalized;
    }

    private function normalizeValue(string|array $value) : array
    {
        if (is_array($value)) {
            return array_values(array_map('trim', $value));
        }

        return [trim((string)$value)];
    }
}
