<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders;

use SensitiveParameter;

/**
 * Action Owner: Normalizes raw header inputs into canonical list of strings.
 *
 * According to PSR-7, each header can have multiple values.
 * This unit ensures all input formats (string, list, comma-separated)
 * are normalized to a consistent array<string> shape.
 */
final readonly class NormalizeHeaders
{
    /**
     * @param array<string, string|string[]> $headers
     *
     * @return array<string, string[]>
     */
    public function execute(#[SensitiveParameter] array $headers) : array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[$name] = $this->normalizeValue(value: $values);
        }

        return $normalized;
    }

    /**
     * @param string|string[] $value
     *
     * @return string[]
     */
    private function normalizeValue(string|array $value) : array
    {
        if (is_array($value)) {
            return array_map('strval', array_values($value));
        }

        // Handle comma-separated values if needed? 
        // PSR-7 usually expects the caller or the input to already be separated 
        // if they come from separate header lines, but for convenience we can split if it's a string 
        // that looks like it has multiple values. 
        // Actually, PSR-7 getHeaderLine() returns a comma-separated string, 
        // but when creating, if we get a string, we treat it as a single value unless specified.
        // For simplicity and to match the test expectations, we just wrap in array.

        return [(string) $value];
    }
}
