<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\SendResponse;

use Avax\Components\HTTP\System\Capabilities\Body\StreamBody;
use Avax\Components\HTTP\System\Capabilities\Response;

final class SendResponse
{
    public static function execute(Response $response): void
    {
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();

        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        header("{$protocol} {$status} {$reason}");

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", true);
            }
        }

        $body = $response->getBody();
        if ($body instanceof StreamBody) {
            echo $body->getContents();
        }
    }
}
