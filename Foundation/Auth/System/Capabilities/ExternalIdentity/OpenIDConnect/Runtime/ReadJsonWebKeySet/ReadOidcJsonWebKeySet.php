<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadJsonWebKeySet;

use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderInterface;

final readonly class ReadOidcJsonWebKeySet
{
    public function __construct(private OidcProviderInterface $oidcProvider)
    {
    }

    public function execute() : OidcJsonWebKeySet
    {
        return $this->oidcProvider->readJsonWebKeySet();
    }
}
