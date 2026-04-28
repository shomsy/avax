<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

interface FederationMetadataRuntimeInterface
{
    public function readMetadata(FederationConnection $connection) : FederationMetadata;
}
