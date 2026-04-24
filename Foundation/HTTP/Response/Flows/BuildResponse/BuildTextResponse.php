<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;

final class BuildTextResponse
{
    public function __invoke(string $content, int|null $status = null, array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildResponse()(
            status : $status,
            headers: ['Content-Type' => 'text/plain; charset=UTF-8', ...$headers],
            body   : $content,
        );
    }
}
