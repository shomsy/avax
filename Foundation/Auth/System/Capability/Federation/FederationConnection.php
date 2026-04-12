<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

final readonly class FederationConnection
{
    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $connectionId,
        public string $tenantSlug,
        public string $name,
        public FederationProvider $provider,
        public string $domain,
        public bool $ssoOnly = false,
        public array $groupRoleMap = []
    ) {}
}
