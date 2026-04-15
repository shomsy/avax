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
        if ($value === null || $value === '') {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $result ?? $default;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $body = $this->extractBodyArray();

        // Priority: Body > Query
        return $body[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function int(string $key, int $default = 0) : int
    {
        $value = $this->get(key: $key);
        if ($value === null || $value === '') {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_INT);

        return ($result === false) ? $default : $result;
    }

    public function float(string $key, float $default = 0.0) : float
    {
        $value = $this->get(key: $key);
        if ($value === null || $value === '') {
            return $default;
        }

        $result = filter_var($value, FILTER_VALIDATE_FLOAT);

        return ($result === false) ? $default : $result;
    }

    public function text(string $key, string $default = '') : string
    {
        $value = $this->get(key: $key);
        if ($value === null) {
            return $default;
        }

        return (string) $value;
    }

    /**
     * @return array<mixed>
     */
    public function list(string $key, array $default = []) : array
    {
        $value = $this->get(key: $key);
        if ($value === null) {
            return $default;
        }

        return is_array($value) ? $value : [$value];
    }
}
