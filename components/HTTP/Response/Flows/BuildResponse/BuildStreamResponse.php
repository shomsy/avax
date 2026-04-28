<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildStreamResponse
{
    public function __invoke(mixed $stream, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildResponse()(
            status : $status,
            headers: $headers,
            body   : $stream,
        );
    }
}
