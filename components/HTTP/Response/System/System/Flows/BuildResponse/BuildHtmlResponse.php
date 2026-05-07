<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\System\PublicSurface\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;

/**
 * Build an HTML HTTP response.
 */
final class BuildHtmlResponse
{
    public static function execute(string $content, int $status = 200) : ResponseInterface
    {
        return new Response(
            statusCode: $status,
            headers   : ['content-type' => ['text/html; charset=UTF-8']],
            stream    : Utils::streamFor($content),
        );
    }
}
