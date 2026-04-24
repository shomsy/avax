<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;

final class BuildStreamResponse
{
    public function __invoke(mixed $stream, int $status = 200, array $headers = []) : ResponseInterface
    {
        return (new BuildResponse())(
            status : $status,
            headers: $headers,
            body   : $stream,
        );
    }
}
