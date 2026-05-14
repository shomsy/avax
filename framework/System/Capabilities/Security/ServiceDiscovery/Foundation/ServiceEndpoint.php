<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation;

final readonly class ServiceEndpoint
{
    /**
     * @param array<string, string> $metadata
     *
     * @throws \InvalidArgumentException When the URL is invalid
     */
    public function __construct(
        public string $url,
        /** @var array<string, string> $metadata */
        public array $metadata = [],
    ) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid service endpoint URL: {$url}");
        }
    }
}
