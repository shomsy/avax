<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody;

/**
 * ParsedBody
 *
 * Capability owner for the interpreted request body.
 *
 * Semantic rules:
 * - parsed body may be an array, object, or null
 * - null means "no parsed body is available"
 * - this object is immutable
 */
final readonly class ParsedBody
{
    /**
     * @param array|object|null $data
     */
    public function __construct(private array|object|null $data = null) {}

    public static function empty() : self
    {
        return new self();
    }

    public function data() : array|object|null
    {
        return $this->data;
    }

    public function isEmpty() : bool
    {
        return $this->data === null;
    }

    public function withData(array|object|null $data) : self
    {
        return new self(data: $data);
    }
}