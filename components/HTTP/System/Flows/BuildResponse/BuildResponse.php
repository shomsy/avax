<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use GuzzleHttp\Psr7\Utils;

final class BuildResponse
{
    /**
     * @param array<string, string|string[]> $headers
     */
    public static function execute(
        int     $status = 200,
        array $headers = [], string|null $body = null,
    ) : Response
    {
        return new Response(
            statusCode: $status,
            headers   : $headers,
            stream    : Utils::streamFor(resource: $body ?? ''),
        );
    }
}
