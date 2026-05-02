<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\BuildResponse;

use Avax\Components\HTTP\System\Capabilities\Body\StreamBody;
use Avax\Components\HTTP\System\Capabilities\Response;

final class BuildResponse
{
    public static function execute(
        int $status = 200,
        array $headers = [],
        ?string $body = null,
    ): Response {
        $response = new Response(
            new StreamBody($body ?? ''),
            $status,
        );

        foreach ($headers as $name => $value) {
            $response->withHeader($name, $value);
        }

        return $response;
    }
}
