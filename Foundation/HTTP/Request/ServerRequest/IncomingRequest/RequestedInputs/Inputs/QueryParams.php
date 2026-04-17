<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

/**
 * QueryParams
 *
 * Strongly-typed wrapper for query parameter input.
 */
final readonly class QueryParams
{
    use AccessesTypedValues;

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        private array $params = [],
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params) : self
    {
        return new self(params: $params);
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->params;
    }

    /**
     * @return array<string, mixed>
     */
    protected function data() : array
    {
        return $this->params;
    }
}
