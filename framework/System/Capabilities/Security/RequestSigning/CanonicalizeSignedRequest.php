<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning;

final readonly class CanonicalizeSignedRequest
{
    /**
     * Create a canonical string from method, path, body, and headers for signing.
     *
     * @param array<string, string> $headers
     */
    public static function canonical(
        string $method,
        string $path,
        string $body = '',
        array $headers = [],
    ): string {
        $method = strtoupper($method);

        $canonicalPath = self::canonicalizePath($path);

        $sortedHeaders = $headers;
        ksort($sortedHeaders, SORT_STRING);

        $headerLines = [];
        foreach ($sortedHeaders as $name => $value) {
            $lowerName = strtolower($name);
            if (str_starts_with($lowerName, 'x-avax-signature-') || $lowerName === 'x-avax-signature') {
                continue;
            }
            $headerLines[] = "{$lowerName}:" . self::trimValue($value);
        }

        $headerBlock = implode("\n", $headerLines);

        $bodyHash = hash('sha256', $body);

        return implode("\n", [
            $method,
            $canonicalPath,
            $headerBlock,
            $bodyHash,
        ]);
    }

    private static function canonicalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $parts = explode('/', $path);
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($resolved);
                continue;
            }
            $resolved[] = $part;
        }

        return '/' . implode('/', $resolved);
    }

    private static function trimValue(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
