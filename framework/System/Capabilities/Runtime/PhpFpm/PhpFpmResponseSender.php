<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\PhpFpm;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;

final readonly class PhpFpmResponseSender
{
    public function send(RuntimeResponse $runtimeResponse): void
    {
        http_response_code(response_code: $runtimeResponse->statusCode());

        foreach ($runtimeResponse->headers() as $name => $values) {
            foreach ($values as $value) {
                header(header: sprintf('%s: %s', $name, $value), replace: false);
            }
        }

        echo $runtimeResponse->body();
    }
}
