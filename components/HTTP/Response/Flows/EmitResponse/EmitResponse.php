<?php

declare(strict_types=1);

namespace components\HTTP\Response\Flows\EmitResponse;

use Psr\Http\Message\ResponseInterface;

final class EmitResponse
{
    public function __invoke(ResponseInterface $response) : void
    {
        if (! headers_sent()) {
            new EmitResponseStatus()(response: $response);
            new EmitResponseHeaders()(response: $response);
        }

        new EmitResponseBody()(response: $response);
    }
}
