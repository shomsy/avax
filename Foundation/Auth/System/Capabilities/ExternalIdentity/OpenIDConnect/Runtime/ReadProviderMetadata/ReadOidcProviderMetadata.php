<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadProviderMetadata;

use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;

final readonly class ReadOidcProviderMetadata
{
    private OidcProviderInterface $oidcProvider;

    public function __construct(
        OidcProviderInterface $oidcProvider
    )
    {
        $this->oidcProvider = $oidcProvider;
    }

    public function execute() : OidcProviderMetadata
    {
        return $this->oidcProvider->readProviderMetadata();
    }
}
