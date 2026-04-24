<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;

final class BuildHtmlResponse
{
    public function __invoke(string $content, int $status = 200, array $headers = []) : ResponseInterface
    {
        return (new BuildResponse())(
            status : $status,
            headers: ['Content-Type' => 'text/html; charset=UTF-8', ...$headers],
            body   : $content,
        );
    }
}
