<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ReadJsonWebKeySet;

use Avax\Auth\System\Capability\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;

final readonly class ReadOidcJsonWebKeySet
{
    private OidcProviderInterface $oidcProvider;

    public function __construct(
        OidcProviderInterface $oidcProvider
    )
    {
        $this->oidcProvider = $oidcProvider;
    }

    public function execute() : OidcJsonWebKeySet
    {
        return $this->oidcProvider->readJsonWebKeySet();
    }
}
