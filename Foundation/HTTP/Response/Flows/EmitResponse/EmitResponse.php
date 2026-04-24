<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\EmitResponse;

use Psr\Http\Message\ResponseInterface;

final class EmitResponse
{
    public function __invoke(ResponseInterface $response) : void
    {
        if (! headers_sent()) {
            (new EmitResponseStatus())($response);
            (new EmitResponseHeaders())($response);
        }

        (new EmitResponseBody())($response);
    }
}
