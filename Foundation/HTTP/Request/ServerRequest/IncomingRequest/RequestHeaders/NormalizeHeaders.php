<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders;

use SensitiveParameter;

/**
 * Normalizes raw header input into a stable internal representation.
 */
final class NormalizeHeaders
{
    /**
     * @param array<string, string|array<int, string>> $headers
     * @return array<string, string[]>
     */
    public static function normalizeHeaders(#[SensitiveParameter] array $headers): array
    {
        $normalizedHeaders = [];
        $canonicalNames = [];

        foreach ($headers as $name => $value) {
            $normalizedHeader = self::normalizeHeader(name: $name, value: $value);

            if ($normalizedHeader === null) {
                continue;
            }

            $lookupKey = strtolower($normalizedHeader['name']);

            if (! isset($canonicalNames[$lookupKey])) {
                $canonicalNames[$lookupKey] = $normalizedHeader['name'];
                $normalizedHeaders[$normalizedHeader['name']] = $normalizedHeader['values'];

                continue;
            }

            $canonicalName = $canonicalNames[$lookupKey];
            $normalizedHeaders[$canonicalName] = [
                ...$normalizedHeaders[$canonicalName],
                ...$normalizedHeader['values'],
            ];
        }

        return $normalizedHeaders;
    }

    /**
     * @return array{name: string, values: string[]}|null
     */
    public static function normalizeHeader(string $name, string|array $value): array|null
    {
        $normalizedName = self::normalizeHeaderName(name: $name);

        if ($normalizedName === null) {
            return null;
        }

        return [
            'name' => $normalizedName,
            'values' => self::normalizeValues(value: $value),
        ];
    }

    /**
     * @param array<string, string[]> $normalizedHeaders
     * @return array<string, string>
     */
    public static function buildLookupMap(#[SensitiveParameter] array $normalizedHeaders): array
    {
        $nameMap = [];

        foreach ($normalizedHeaders as $name => $_) {
            $nameMap[strtolower($name)] = $name;
        }

        return $nameMap;
    }

    private static function normalizeHeaderName(string $name): string|null
    {
        $normalizedName = trim($name);

        return $normalizedName === ''
            ? null
            : $normalizedName;
    }

    /**
     * @param string|array<int, string> $value
     * @return string[]
     */
    private static function normalizeValues(string|array $value): array
    {
        if (is_string($value)) {
            return self::splitHeaderValue(value: $value);
        }

        $normalizedValues = [];

        foreach (array_values($value) as $item) {
            $normalizedValues = [
                ...$normalizedValues,
                ...self::splitHeaderValue(value: $item),
            ];
        }

        return $normalizedValues;
    }

    /**
     * @return string[]
     */
    private static function splitHeaderValue(string $value): array
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return [''];
        }

        $segments = array_map(
            static fn (string $segment): string => trim($segment),
            explode(',', $trimmed),
        );

        return array_values(
            array_filter(
                $segments,
                static fn (string $segment): bool => $segment !== '',
            )
        );
    }
}
