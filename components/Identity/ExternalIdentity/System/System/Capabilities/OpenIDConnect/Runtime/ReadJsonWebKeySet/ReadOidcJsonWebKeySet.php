<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Runtime\ReadJsonWebKeySet;

use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol\OidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;

final readonly class ReadOidcJsonWebKeySet
{
    public function __construct(private OidcProviderInterface $oidcProvider) {}

    public function execute() : OidcJsonWebKeySet
    {
        return $this->oidcProvider->readJsonWebKeySet();
    }
}
