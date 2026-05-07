<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Build an empty HTTP response (PSR-17 createResponse).
 */
final class BuildEmptyResponse
{
    public static function execute(int $status = 200, string $reasonPhrase = '') : ResponseInterface
    {
        return new Response(
            statusCode  : $status,
            reasonPhrase: $reasonPhrase,
        );
    }
}
