<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestedInputs;

/**
 * Capability Owner: Provides a DSL-friendly way to access request inputs.
 *
 * Aggregates Query Parameters and Parsed Body data.
 */
final readonly class RequestedInputs
{
    private array|null|object $parsedBody;
    private array             $queryParams;

    /**
     * @param array<string, mixed> $queryParams
     * @param array|object|null    $parsedBody
     */
    public function __construct(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null
    )
    {
        $queryParams       ??= [];
        $this->queryParams = $queryParams;
        $this->parsedBody  = $parsedBody;
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        $body = $this->extractBodyArray();

        // Body takes precedence over query in case of collisions
        return array_merge($this->queryParams, $body);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractBodyArray() : array
    {
        if (is_array($this->parsedBody)) {
            return $this->parsedBody;
        }

        if (is_object($this->parsedBody)) {
            return (array) $this->parsedBody;
        }

        return [];
    }

    public function bool(string $key, bool $default = false) : bool
    {
        $value = $this->get(key: $key);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $body = $this->extractBodyArray();

        return $body[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function int(string $key, int $default = 0) : int
    {
        $value = $this->get(key: $key);
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }
}
