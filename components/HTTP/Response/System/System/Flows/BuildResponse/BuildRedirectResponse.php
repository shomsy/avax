<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\System\Flows\BuildResponse;

use Avax\Components\HTTP\Response\System\System\PublicSurface\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Build an HTTP redirect response.
 */
final class BuildRedirectResponse
{
    public static function execute(string $target, int $status = 302) : ResponseInterface
    {
        return new Response(
            statusCode: $status,
            headers   : ['location' => [$target]],
        );
    }
}
