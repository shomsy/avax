<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

/**
 * ParsedBody
 *
 * Strongly-typed wrapper for parsed body input.
 *
 * Semantic rules:
 * - internal state is always a normalized array
 * - null becomes []
 * - arrays stay unchanged
 * - objects are reduced to their public properties via get_object_vars()
 */
final readonly class ParsedBody
{
    use AccessesTypedValues;

    /**
     * @param array<string, mixed> $data
     */
    private function __construct(private array $data = []) {}

    public static function empty() : self
    {
        return new self();
    }

    public static function fromParsedBody(array|object|null $data) : self
    {
        return new self(
            data: self::normalize(data: $data),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalize(array|object|null $data) : array
    {
        if ($data === null) {
            return [];
        }

        if (is_array(value: $data)) {
            return $data;
        }

        return get_object_vars(object: $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function data() : array
    {
        return $this->data;
    }
}