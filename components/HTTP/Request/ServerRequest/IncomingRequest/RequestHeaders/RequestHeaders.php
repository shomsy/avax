<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders;

use NoDiscard;
use SensitiveParameter;

final class RequestHeaders
{
    /** @var array<string, string[]> */
    private array $headers
        = [] {
            set => self::normalizeHeaders(headers: $value);
        }

    /** @var array<string, string>|null */
    private array|null $nameMapCache = null;

    /**
     * @var array<string, string>
     */
    private array $nameMap {
        get => $this->nameMapCache ??= self::buildLookupMap(
            normalizedHeaders: $this->headers,
        );
    }

    public function __construct(#[SensitiveParameter] array $headersInput = [])
    {
        $this->headers = $headersInput;
    }

    /**
     * @param array<string, string[]> $normalizedHeaders
     *
     * @return array<string, string>
     */
    private static function buildLookupMap(#[SensitiveParameter] array $normalizedHeaders) : array
    {
        return NormalizeHeaders::buildLookupMap(normalizedHeaders: $normalizedHeaders);
    }

    /**
     * @return array<string, string[]>
     */
    public function all() : array
    {
        return $this->headers;
    }

    public function has(string $name) : bool
    {
        return isset($this->nameMap[strtolower(string: $name)]);
    }

    public function getLine(string $name) : string
    {
        return implode(separator: ',', array: $this->get(name: $name));
    }

    /**
     * @return string[]
     */
    public function get(string $name) : array
    {
        $canonicalName = $this->nameMap[strtolower(string: $name)] ?? null;

        return $canonicalName === null
            ? []
            : $this->headers[$canonicalName];
    }

    #[NoDiscard(message: 'RequestHeaders is immutable; use the returned instance.')]
    public function append(string $name, string|array $value) : self
    {
        [, $toAdd] = self::normalizeSingleHeader(name: $name, value: $value);

        return $this->put(
            name : $name,
            value: [...$this->get(name: $name), ...$toAdd],
        );
    }

    /**
     * @return array{0: string, 1: string[]}
     */
    private static function normalizeSingleHeader(string $name, string|array $value) : array
    {
        $normalized    = self::normalizeHeaders(headers: [$name => $value]);
        $canonicalName = array_key_first(array: $normalized);

        return $canonicalName === null
            ? [$name, []]
            : [$canonicalName, $normalized[$canonicalName]];
    }

    /**
     * @param array<string, string|array<int, string>> $headers
     *
     * @return array<string, string[]>
     */
    private static function normalizeHeaders(#[SensitiveParameter] array $headers) : array
    {
        return NormalizeHeaders::normalizeHeaders(headers: $headers);
    }

    #[NoDiscard(message: 'RequestHeaders is immutable; use the returned instance.')]
    public function put(string $name, string|array $value) : self
    {
        $headers      = $this->headers;
        $existingName = $this->nameMap[strtolower(string: $name)] ?? null;

        if ($existingName !== null) {
            unset($headers[$existingName]);
        }

        [$canonicalName, $normalizedValues] = self::normalizeSingleHeader(
            name : $name,
            value: $value,
        );

        $headers[$canonicalName] = $normalizedValues;

        return $this->withHeaders(headers: $headers);
    }

    /**
     * @param array<string, string|array<int, string>> $headers
     */
    private function withHeaders(#[SensitiveParameter] array $headers) : self
    {
        $clone               = clone $this;
        $clone->headers      = $headers;
        $clone->nameMapCache = null;

        return $clone;
    }

    #[NoDiscard(message: 'RequestHeaders is immutable; use the returned instance.')]
    public function drop(string $name) : self
    {
        $existingName = $this->nameMap[strtolower(string: $name)] ?? null;

        if ($existingName === null) {
            return $this;
        }

        $headers = $this->headers;
        unset($headers[$existingName]);

        return $this->withHeaders(headers: $headers);
    }
}
