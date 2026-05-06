<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadProviderMetadata;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderMetadata;

final readonly class ReadOidcProviderMetadata
{
    public function __construct(private OidcProviderInterface $oidcProvider)
    {
    }

    public function execute(): OidcProviderMetadata
    {
        return $this->oidcProvider->readProviderMetadata();
    }
}
