<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\ReactPhp;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ConvertReactRequestToAvaxRequest — Converts a PSR-7 ServerRequest (from ReactPHP)
 * into an AvaX RuntimeRequest.
 */
final readonly class ConvertReactRequestToAvaxRequest
{
    public function convert(ServerRequestInterface $request): RuntimeRequest
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = array_values($values);
        }

        return new RuntimeRequest(
            method: $request->getMethod(),
            uri: (string) $request->getUri(),
            headers: $headers,
            body: (string) $request->getBody(),
        );
    }
}
