<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Body;

final readonly class RequestBody
{
    public function __construct(
        private RawBody    $rawBody,
        private ParsedBody $parsedBody,
    ) {}

    public function raw() : RawBody
    {
        return $this->rawBody;
    }

    public function parsed() : ParsedBody
    {
        return $this->parsedBody;
    }
}
