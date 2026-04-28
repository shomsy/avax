<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

/**
 * QueryParams
 *
 * Strongly-typed wrapper for query parameter input.
 *
 * Semantic rules:
 * - internal state is always an array
 * - query params are read-only
 * - presence semantics are delegated to AccessesTypedValues
 */
final readonly class QueryParams
{
    use AccessesTypedValues;

    /**
     * @param array<string, mixed> $params
     */
    private function __construct(private array $params = []) {}

    public static function empty() : self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function fromQueryParams(array $params) : self
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