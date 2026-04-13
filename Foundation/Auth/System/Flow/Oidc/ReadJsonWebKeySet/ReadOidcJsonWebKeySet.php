<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ReadJsonWebKeySet;

use Avax\Auth\System\Capability\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;

final readonly class ReadOidcJsonWebKeySet
{
    public function __construct(
        private OidcProviderInterface $oidcProvider
    ) {}

    public function execute() : OidcJsonWebKeySet
    {
        return $this->oidcProvider->readJsonWebKeySet();
    }
}
