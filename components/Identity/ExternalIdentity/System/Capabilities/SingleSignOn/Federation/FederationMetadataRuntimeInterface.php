<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

interface FederationMetadataRuntimeInterface
{
    public function readMetadata(FederationConnection $federationConnection): FederationMetadata;
}
