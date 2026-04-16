<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody;

/**
 * Capability Owner: Manages the interpreted/parsed request body.
 */
final readonly class ParsedBody
{
    private array|object|null $data;

    /**
     * @param array|object|null $data
     */
    public function __construct(array|object|null $data = null)
    {
        $this->data = $data;
    }

    /**
     * @return array|object|null
     */
    public function data() : array|object|null
    {
        return $this->data;
    }

    public function with(array|object|null $data) : self
    {
        return new self(data: $data);
    }
}
