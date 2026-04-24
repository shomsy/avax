<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

/**
 * Case-insensitive immutable HTTP header bag.
 */
final class ResponseHeaders
{
    /**
     * @var array<string, array{name: string, values: list<string>}>
     */
    private array $headers = [];

    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->headers = $this->replace(name: (string) $name, value: $value)->headers;
        }
    }

    public function replace(string $name, mixed $value) : self
    {
        $clone                           = clone $this;
        $originalName = new ValidateHeaderName()($name);
        $normalizedName                  = strtolower(string: $originalName);
        $clone->headers[$normalizedName] = [
            'name'   => $originalName,
            'values' => new NormalizeHeaderValues()($value),
        ];

        return $clone;
    }

    public function has(string $name) : bool
    {
        $normalized = new NormalizeHeaderName()($name);

        return array_key_exists(key: $normalized, array: $this->headers);
    }

    public function readLine(string $name) : string
    {
        return implode(separator: ', ', array: $this->read(name: $name));
    }

    /**
     * @return list<string>
     */
    public function read(string $name) : array
    {
        $normalized = new NormalizeHeaderName()($name);

        return $this->headers[$normalized]['values'] ?? [];
    }

    public function append(string $name, mixed $value) : self
    {
        $clone                           = clone $this;
        $originalName = new ValidateHeaderName()($name);
        $normalizedName                  = strtolower(string: $originalName);
        $existingValues                  = $clone->headers[$normalizedName]['values'] ?? [];
        $clone->headers[$normalizedName] = [
            'name'   => $clone->headers[$normalizedName]['name'] ?? $originalName,
            'values' => array_merge($existingValues, new NormalizeHeaderValues()($value)),
        ];

        return $clone;
    }

    public function remove(string $name) : self
    {
        $clone      = clone $this;
        $normalized = new NormalizeHeaderName()($name);
        unset($clone->headers[$normalized]);

        return $clone;
    }

    public function toArray() : array
    {
        $result = [];
        foreach ($this->headers as $header) {
            $result[$header['name']] = $header['values'];
        }

        return $result;
    }
}
