<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\Parts;

use Stringable;

/**
 * Represents URI query parameters, immutable.
 */
final readonly class Query implements Stringable
{
    /** @var array<string, string|array<string>> */
    private array $params;

    public function __construct(string $queryString = '')
    {
        $this->params = $queryString !== '' ? $this->parse(queryString: $queryString) : [];
    }

    private function parse(string $queryString) : array
    {
        $params = [];
        parse_str($queryString, $params);

        return $params;
    }

    public function add(string $key, string $value) : self
    {
        $params = $this->params;
        if (! isset($params[$key])) {
            $params[$key] = $value;
        } elseif (is_array($params[$key])) {
            $params[$key][] = $value;
        } else {
            $params[$key] = [$params[$key], $value];
        }

        return new self(queryString: $this->buildQuery(params: $params));
    }

    private function buildQuery(array $params) : string
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function set(string $key, string $value) : self
    {
        $params       = $this->params;
        $params[$key] = $value;

        return new self(queryString: $this->buildQuery(params: $params));
    }

    public function remove(string $key) : self
    {
        $params = $this->params;
        unset($params[$key]);

        return new self(queryString: $this->buildQuery(params: $params));
    }

    public function clear() : self
    {
        return new self();
    }

    public function get(string $key) : string|array|null
    {
        return $this->params[$key] ?? null;
    }

    public function all() : array
    {
        return $this->params;
    }

    public function __toString() : string
    {
        return $this->buildQuery(params: $this->params);
    }
}