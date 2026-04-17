<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Pipeline;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\ParsedBody;

final readonly class ReadParsedBody
{
    /**
     * @param array|object|null $parsedBody
     */
    public function __construct(private array|object|null $parsedBody) {}

    public function get() : ParsedBody
    {
        return ParsedBody::fromArray(data: $this->parsedBody);
    }
}
