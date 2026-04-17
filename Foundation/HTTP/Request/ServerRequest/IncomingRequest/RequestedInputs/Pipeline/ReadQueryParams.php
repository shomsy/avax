<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Pipeline;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\QueryParams;

final readonly class ReadQueryParams
{
    /**
     * @param array<string, mixed> $queryParams
     */
    public function __construct(private array $queryParams) {}

    public function get() : QueryParams
    {
        return QueryParams::fromArray(params: $this->queryParams);
    }
}
