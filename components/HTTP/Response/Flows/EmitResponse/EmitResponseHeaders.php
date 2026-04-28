<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\EmitResponse;

use Psr\Http\Message\ResponseInterface;

final class EmitResponseHeaders
{
    public function __invoke(ResponseInterface $response) : void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(header: "{$name}: {$value}", replace: false);
            }
        }
    }
}
