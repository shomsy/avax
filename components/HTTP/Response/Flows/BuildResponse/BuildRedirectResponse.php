<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Redirects\NormalizeRedirectStatus;
use Avax\HTTP\Response\Capabilities\Redirects\ValidateRedirectTarget;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildRedirectResponse
{
    public function __invoke(string $target, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 302;

        return new BuildEmptyResponse()(
            status : new NormalizeRedirectStatus()(status: $status),
            headers: ['Location' => new ValidateRedirectTarget()(target: $target), ...$headers],
        );
    }
}
