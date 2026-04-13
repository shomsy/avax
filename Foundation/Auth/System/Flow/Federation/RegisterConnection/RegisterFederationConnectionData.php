<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\RegisterConnection;

use Avax\Auth\System\Capability\Federation\FederationProvider;

final readonly class RegisterFederationConnectionData
{
    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $tenantSlug,
        public string $name,
        public FederationProvider $provider,
        public string $domain,
        public bool $ssoOnly = false,
        public array $groupRoleMap = [],
        public string|null $metadataUrl = null,
        public bool $breakGlassAllowed = false
    ) {}
}
