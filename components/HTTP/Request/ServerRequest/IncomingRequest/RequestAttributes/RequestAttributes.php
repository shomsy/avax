<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes;

/**
 * RequestAttributes - Capability Owner: Manages arbitrary request attributes.
 *
 * Semantic rules:
 * - key presence uses `array_key_exists()`. An attribute can be explicitly set to `null`.
 * - `has()` determines presence, regardless of nullability.
 */
final readonly class RequestAttributes
{
    public function __construct(private array $attributes = []) {}

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->attributes;
    }

    public function has(string $name) : bool
    {
        return array_key_exists(key: $name, array: $this->attributes);
    }

    public function get(string $name, mixed $default = null) : mixed
    {
        return array_key_exists(key: $name, array: $this->attributes)
            ? $this->attributes[$name]
            : $default;
    }

    public function put(string $name, mixed $value) : self
    {
        $newAttributes        = $this->attributes;
        $newAttributes[$name] = $value;

        return new self(attributes: $newAttributes);
    }

    public function drop(string $name) : self
    {
        $newAttributes = $this->attributes;
        unset($newAttributes[$name]);

        return new self(attributes: $newAttributes);
    }
}
