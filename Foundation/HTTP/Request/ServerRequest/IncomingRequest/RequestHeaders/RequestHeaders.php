<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders;

use SensitiveParameter;

/**
 * Capability Owner: Manages HTTP request headers.
 * Provides immutable, case-insensitive access according to PSR-7 rules.
 */
final readonly class RequestHeaders
{
    /** @var array<string, string[]> */
    private array $headers;

    /** @var array<string, string> */
    private array $nameMap;

    public function __construct(#[SensitiveParameter] array $headers = [])
    {
        $normalized    = (new NormalizeHeaders())->execute(headers: $headers);
        $this->headers = $normalized;

        $nameMap = [];
        foreach (array_keys($normalized) as $name) {
            $nameMap[strtolower($name)] = $name;
        }
        $this->nameMap = $nameMap;
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
        return isset($this->nameMap[strtolower($name)]);
    }

    public function getLine(string $name) : string
    {
        $values = $this->get(name: $name);
        if ($values === []) {
            return '';
        }

        return implode(', ', $values);
    }

    /**
     * @return string[]
     */
    public function get(string $name) : array
    {
        $normalizedName = strtolower($name);
        if (! isset($this->nameMap[$normalizedName])) {
            return [];
        }

        return $this->headers[$this->nameMap[$normalizedName]];
    }

    public function append(string $name, string|array $value) : self
    {
        $current = $this->get(name: $name);
        $toAdd   = (new NormalizeHeaders())->execute(headers: [$name => $value])[$name];

        return $this->put(name: $name, value: array_merge($current, $toAdd));
    }

    public function put(string $name, string|array $value) : self
    {
        $newHeaders     = $this->headers;
        $normalizedName = strtolower($name);

        // Remove old name if case differs
        if (isset($this->nameMap[$normalizedName])) {
            unset($newHeaders[$this->nameMap[$normalizedName]]);
        }

        $newHeaders[$name] = (new NormalizeHeaders())->execute(headers: [$name => $value])[$name];

        return new self(headers: $newHeaders);
    }

    public function drop(string $name) : self
    {
        $normalizedName = strtolower($name);
        if (! isset($this->nameMap[$normalizedName])) {
            return $this;
        }

        $newHeaders = $this->headers;
        unset($newHeaders[$this->nameMap[$normalizedName]]);

        return new self(headers: $newHeaders);
    }
}
