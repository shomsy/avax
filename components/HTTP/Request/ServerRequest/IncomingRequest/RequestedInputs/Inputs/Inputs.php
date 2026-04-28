<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

/**
 * Inputs
 *
 * Immutable merged view over query params and parsed body.
 *
 * Merge rule:
 * - body wins over query when the same key exists in both places
 */
final class Inputs
{
    use AccessesTypedValues;

    /** @var array<string, mixed>|null */
    private array|null $valuesCache = null;

    /**
     * Lazily built merged view.
     *
     * Body values override query values for the same key.
     *
     * @var array<string, mixed>
     */
    private array $values {
        get => $this->valuesCache ??= array_replace(
            $this->queryParams->all(),
            $this->parsedBody->all(),
        );
    }

    public function __construct(
        private readonly QueryParams $queryParams = new QueryParams(),
        private readonly ParsedBody  $parsedBody = new ParsedBody(),
    ) {}

    public static function empty() : self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    public static function fromQueryAndBody(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null,
    ) : self
    {
        $queryParams ??= [];

        return new self(
            queryParams: QueryParams::fromQueryParams(params: $queryParams),
            parsedBody : ParsedBody::fromParsedBody(data: $parsedBody),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->values;
    }

    public function query() : QueryParams
    {
        return $this->queryParams;
    }

    public function body() : ParsedBody
    {
        return $this->parsedBody;
    }

    public function isEmpty() : bool
    {
        return $this->values === [];
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

    public function has(string $key) : bool
    {
        return $this->hasInBody(key: $key) || $this->hasInQuery(key: $key);
    }

    public function hasInBody(string $key) : bool
    {
        return $this->parsedBody->has(key: $key);
    }

    public function hasInQuery(string $key) : bool
    {
        return $this->queryParams->has(key: $key);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        if ($this->parsedBody->has(key: $key)) {
            return $this->parsedBody->get(key: $key);
        }

        if ($this->queryParams->has(key: $key)) {
            return $this->queryParams->get(key: $key);
        }

        return $default;
    }

    public function bodyValue(string $key, mixed $default = null) : mixed
    {
        return $this->parsedBody->get(key: $key, default: $default);
    }

    public function queryValue(string $key, mixed $default = null) : mixed
    {
        return $this->queryParams->get(key: $key, default: $default);
    }

    /**
     * @return array<string, mixed>
     */
    protected function data() : array
    {
        return $this->values;
    }
}