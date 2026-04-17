<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Pipeline;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\MergedInputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\QueryParams;

final readonly class MergeRequestedInputs
{
    public function __construct(
        private QueryParams $queryParams,
        private ParsedBody  $parsedBody,
    ) {}

    public function get() : MergedInputs
    {
        return new MergedInputs(
            queryParams: $this->queryParams,
            parsedBody : $this->parsedBody,
        );
    }
}
