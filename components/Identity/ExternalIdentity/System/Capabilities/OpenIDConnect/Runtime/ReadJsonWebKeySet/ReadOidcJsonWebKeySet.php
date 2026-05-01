<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadJsonWebKeySet;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcProviderInterface;

final readonly class ReadOidcJsonWebKeySet
{
    public function __construct(private OidcProviderInterface $oidcProvider) {}

    public function execute() : OidcJsonWebKeySet
    {
        return $this->oidcProvider->readJsonWebKeySet();
    }
}
