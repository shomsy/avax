<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Federation;

interface FederationMetadataRuntimeInterface
{
    public function readMetadata(FederationConnection $connection) : FederationMetadata;
}
