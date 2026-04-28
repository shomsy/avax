<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildNoContentResponse
{
    public function __invoke(#[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        return new BuildEmptyResponse()(status: 204, headers: $headers);
    }
}
