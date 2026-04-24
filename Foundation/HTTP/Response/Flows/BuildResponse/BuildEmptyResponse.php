<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;

final class BuildEmptyResponse
{
    public function __invoke(int $status = 200, array $headers = [], string $reasonPhrase = '') : ResponseInterface
    {
        return (new BuildResponse())(
            status      : $status,
            headers     : $headers,
            body        : '',
            reasonPhrase: $reasonPhrase,
        );
    }
}
