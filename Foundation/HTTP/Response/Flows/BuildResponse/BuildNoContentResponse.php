<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Psr\Http\Message\ResponseInterface;

final class BuildNoContentResponse
{
    public function __invoke(array $headers = []) : ResponseInterface
    {
        return (new BuildEmptyResponse())(status: 204, headers: $headers);
    }
}
