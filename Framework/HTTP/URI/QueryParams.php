<?php

declare(strict_types=1);

namespace Gemini\HTTP\URI;

use InvalidArgumentException;

final class QueryParams
{
    private array $params = [];

    public function __construct(string $queryString = '')
    {
        if ($queryString !== '') {
            parse_str($queryString, $this->params);
        }
    }

    public function set(string $key, string $value) : void
    {
        if ($key === '' || $key === '0') {
            throw new InvalidArgumentException(message: "Query parameter key cannot be empty.");
        }

        $this->params[$key] = $value;
    }

    public function delete(string $key) : void
    {
        unset($this->params[$key]);
    }

    public function append(string $key, string $value) : void
    {
        if (! isset($this->params[$key])) {
            $this->params[$key] = $value;
        } elseif (is_array($this->params[$key])) {
            $this->params[$key][] = $value;
        } else {
            $this->params[$key] = [$this->params[$key], $value];
        }
    }

    public function toArray() : array
    {
        return $this->params;
    }

    public function toString() : string
    {
        $query = [];
        foreach ($this->params as $key => $value) {
            $query[] = sprintf('%s=%s', rawurlencode($key), rawurlencode((string) $value));
        }

        return implode('&', $query);
    }


    public function clear() : void
    {
        $this->params = [];
    }
}
