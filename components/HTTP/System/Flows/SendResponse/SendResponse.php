<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\SendResponse;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;

final class SendResponse
{
    public static function execute(Response $response) : void
    {
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();

        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        header(sprintf('%s %s %s', $protocol, $status, $reason));

        foreach ($response->getHeaders() as $name => $header) {
            foreach ($header as $value) {
                header(sprintf('%s: %s', $name, $value), true);
            }
        }

        echo (string) $response->getBody();
    }
}
