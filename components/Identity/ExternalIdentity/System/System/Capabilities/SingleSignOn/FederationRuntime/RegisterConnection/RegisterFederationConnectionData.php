<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection;

use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationProvider;

final readonly class RegisterFederationConnectionData
{
    /** @var array<string, list<string>> */
    public array $groupRoleMap;

    public bool $ssoOnly;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string             $tenantSlug,
        public string             $name,
        public FederationProvider $provider,
        public string             $domain,
        ?bool                     $ssoOnly = null,
        ?array                    $groupRoleMap = null,
        public ?string            $metadataUrl = null,
        public bool               $breakGlassAllowed = false,
    )
    {
        $ssoOnly            ??= false;
        $groupRoleMap       ??= [];
        $this->ssoOnly      = $ssoOnly;
        $this->groupRoleMap = $groupRoleMap;
    }
}
