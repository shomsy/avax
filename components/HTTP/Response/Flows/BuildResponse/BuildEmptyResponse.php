<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildEmptyResponse
{
    public function __invoke(int|null $status = null, #[SensitiveParameter] array|null $headers = null, string $reasonPhrase = '') : ResponseInterface
    {
        $status  ??= 200;
        $headers ??= [];

        return new BuildResponse()(
            status      : $status,
            headers     : $headers,
            body        : '',
            reasonPhrase: $reasonPhrase,
        );
    }
}
