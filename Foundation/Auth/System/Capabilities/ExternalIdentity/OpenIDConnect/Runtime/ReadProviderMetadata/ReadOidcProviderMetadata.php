<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Oidc\ReadProviderMetadata;

use Avax\Auth\System\Capabilities\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capabilities\Oidc\OidcProviderMetadata;

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
