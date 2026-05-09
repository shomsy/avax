<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\ReactPhp;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use GuzzleHttp\Psr7\Response;

/**
 * ConvertAvaxResponseToReactResponse — Converts an AvaX RuntimeResponse
 * into a PSR-7 ResponseInterface (for ReactPHP).
 */
final class ConvertAvaxResponseToReactResponse
{
    public function convert(RuntimeResponse $response): Response
    {
        return new Response(
            $response->statusCode(),
            $response->headers() ?: [],
            $response->body(),
        );
    }
}
