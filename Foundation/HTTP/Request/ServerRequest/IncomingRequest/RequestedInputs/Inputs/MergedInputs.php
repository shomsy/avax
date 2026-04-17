<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

/**
 * MergedInputs
 *
 * Internal merged view over query params and parsed body.
 *
 * Merge rule:
 * - body wins over query when the same key exists in both places
 */
final readonly class MergedInputs
{
    use AccessesTypedValues;

    private QueryParams $queryParams;

    private ParsedBody $parsedBody;

    /**
     * @var array<string, mixed>
     */
    private array $merged;

    public function __construct(
        QueryParams|null $queryParams = null,
        ParsedBody|null  $parsedBody = null,
    )
    {
        $this->queryParams = $queryParams ?? new QueryParams;
        $this->parsedBody  = $parsedBody ?? new ParsedBody;

        $this->merged = array_merge(
            $this->queryParams->all(),
            $this->parsedBody->all(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->merged;
    }

    public static function fromSlices(
        array             $queryParams = [],
        array|object|null $parsedBody = null,
    ) : self
    {
        return new self(
            queryParams: QueryParams::fromArray(params: $queryParams),
            parsedBody : ParsedBody::fromArray(data: $parsedBody),
        );
    }

    public function query() : QueryParams
    {
        return $this->queryParams;
    }

    public function body() : ParsedBody
    {
        return $this->parsedBody;
    }

    /**
     * @return array<string, mixed>
     */
    public function merged() : array
    {
        return $this->merged;
    }

    public function hasInput() : bool
    {
        return $this->merged !== [];
    }

    public function hasInBody(string $key) : bool
    {
        return $this->parsedBody->has(key: $key);
    }

    public function hasInQuery(string $key) : bool
    {
        return $this->queryParams->has(key: $key);
    }

    public function value(string $key, mixed $default = null) : InputValue
    {
        if ($this->parsedBody->has(key: $key)) {
            return InputValue::fromBody(
                key  : $key,
                value: $this->parsedBody->get(key: $key),
            );
        }

        if ($this->queryParams->has(key: $key)) {
            return InputValue::fromQuery(
                key  : $key,
                value: $this->queryParams->get(key: $key),
            );
        }

        return InputValue::missing(
            key    : $key,
            default: $default,
        );
    }

    public function fromBody(string $key, mixed $default = null) : mixed
    {
        return $this->parsedBody->get(key: $key, default: $default);
    }

    public function fromQuery(string $key, mixed $default = null) : mixed
    {
        return $this->queryParams->get(key: $key, default: $default);
    }

    /**
     * @return array<string, mixed>
     */
    protected function data() : array
    {
        return $this->merged;
    }
}
