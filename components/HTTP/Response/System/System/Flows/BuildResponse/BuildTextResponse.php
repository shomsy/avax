<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\System\PublicSurface\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;

/**
 * Build a plain-text HTTP response.
 */
final class BuildTextResponse
{
    public static function execute(string $content, int $status = 200) : ResponseInterface
    {
        return new Response(
            statusCode: $status,
            headers   : ['content-type' => ['text/plain; charset=UTF-8']],
            stream    : Utils::streamFor($content),
        );
    }
}
