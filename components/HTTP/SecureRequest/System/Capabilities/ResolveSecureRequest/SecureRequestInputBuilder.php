<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest;

use Avax\Components\HTTP\SecureRequest\System\PublicSurface\SecureRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Builds input data for SecureRequest from HTTP request.
 * Reads body, query, route params, and files.
 */
final readonly class SecureRequestInputBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function buildInput(ServerRequestInterface $request) : array
    {
        $input = [];

        // Route parameters
        foreach ($request->getAttributes() as $key => $value) {
            if (is_string($key) && $key !== '') {
                $input[$key] = $value;
            }
        }

        // Query parameters
        $input = [...$input, ...$request->getQueryParams()];

        // Parsed body (already parsed by middleware)
        $body = $request->getParsedBody();
        if (is_array($body)) {
            $input = [...$input, ...$body];
        }

        return $input;
    }
}
