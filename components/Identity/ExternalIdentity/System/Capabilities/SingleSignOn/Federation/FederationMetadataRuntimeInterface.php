<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport;

interface FederationMetadataRuntimeInterface
{
    public function readMetadata(FederationConnection $connection) : FederationMetadata;
}
