<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

/**
 * ParsedBody
 *
 * Strongly-typed wrapper for parsed body input.
 */
final readonly class ParsedBody
{
    use AccessesTypedValues;

    /**
     * @var array<string, mixed>
     */
    private array $data;

    public function __construct(array|object|null $data = null)
    {
        $this->data = self::normalize(data: $data);
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalize(array|object|null $data) : array
    {
        if ($data === null) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        return get_object_vars($data);
    }

    public static function fromArray(array|object|null $data) : self
    {
        return new self(data: $data);
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
