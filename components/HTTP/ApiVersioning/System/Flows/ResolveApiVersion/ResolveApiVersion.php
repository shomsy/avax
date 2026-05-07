<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Flows\ResolveApiVersion;

final readonly class ResolveApiVersion
{
    /**
     * @param array<string, mixed> $headers
     */
    public function resolve(string $url, array $headers, string $default = 'v1') : string
    {
        if (preg_match('#/v(\d+)/#', $url, $matches)) {
            return "v{$matches[1]}";
        }

        if (isset($headers['X-API-Version'])) {
            return "v{$headers['X-API-Version']}";
        }

        return $default;
    }
}
