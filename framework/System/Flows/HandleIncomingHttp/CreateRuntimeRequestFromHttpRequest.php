<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\System\PublicSurface\Request;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;

final readonly class CreateRuntimeRequestFromHttpRequest
{
    public function create(Request $request): RuntimeRequest
    {
        /** @var array<string, list<string>> $headers */
        $headers = $request->getHeaders();

        return new RuntimeRequest(
            method: $request->getMethod(),
            uri    : (string) $request->getUri(),
            headers: $headers,
            body   : (string) $request->getBody(),
        );
    }
}
