<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes;

/**
 * Capability Owner: Manages arbitrary request attributes.
 *
 * Attributes are variables passed to the handler by middleware,
 * authentication flows, or other system participants.
 */
final readonly class RequestAttributes
{
    public function __construct(
        private array $attributes = []
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->attributes;
    }

    public function get(string $name, mixed $default = null) : mixed
    {
        return $this->attributes[$name] ?? $default;
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
