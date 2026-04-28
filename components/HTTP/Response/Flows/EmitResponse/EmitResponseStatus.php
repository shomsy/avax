<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\EmitResponse;

use Psr\Http\Message\ResponseInterface;

final class EmitResponseStatus
{
    public function __invoke(ResponseInterface $response) : void
    {
        http_response_code(response_code: $response->getStatusCode());
    }
}
