<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\System\PublicSurface\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;

/**
 * Build a JSON-encoded HTTP response.
 */
final class BuildJsonResponse
{
    public static function execute(array $data, int $status = 200) : ResponseInterface
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return new Response(
            statusCode: $status,
            headers   : ['content-type' => ['application/json']],
            stream    : Utils::streamFor($json),
        );
    }
}
