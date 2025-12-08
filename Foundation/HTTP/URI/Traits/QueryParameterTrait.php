<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Traits;

use Avax\HTTP\URI\QueryParams;

trait QueryParameterTrait
{
    public function addParam(string $key, string $value) : self
    {
        $clone = clone $this;
        $clone->queryParams->set(rawurldecode($key), rawurldecode($value));

        return $clone;
    }


    public function addParams(array $params) : self
    {
        $clone = clone $this;
        foreach ($params as $key => $value) {
            $clone->queryParams->set($key, $value);
        }

        return $clone;
    }

    public function getParams() : array
    {
        return $this->queryParams->toArray();
    }

    public function removeParam(string $key) : self
    {
        $clone = clone $this;
        $clone->queryParams->delete($key);

        return $clone;
    }

    public function buildQuery() : string
    {
        return $this->queryParams->toString();
    }
}
