<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Redirects\NormalizeRedirectStatus;
use Avax\HTTP\Response\Capabilities\Redirects\ValidateRedirectTarget;
use Psr\Http\Message\ResponseInterface;

final class BuildRedirectResponse
{
    public function __invoke(string $target, int $status = 302, array $headers = []) : ResponseInterface
    {
        return (new BuildEmptyResponse())(
            status : (new NormalizeRedirectStatus())($status),
            headers: ['Location' => (new ValidateRedirectTarget())($target), ...$headers],
        );
    }
}
