<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildTextResponse
{
    public function __invoke(string $content, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildResponse()(
            status : $status,
            headers: ['Content-Type' => 'text/plain; charset=UTF-8', ...$headers],
            body   : $content,
        );
    }
}
