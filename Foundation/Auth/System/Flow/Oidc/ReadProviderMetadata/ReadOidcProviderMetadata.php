<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ReadProviderMetadata;

use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\Oidc\OidcProviderMetadata;

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
