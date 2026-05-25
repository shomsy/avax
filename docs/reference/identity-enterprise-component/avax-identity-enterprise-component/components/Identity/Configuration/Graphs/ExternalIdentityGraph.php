<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Capabilities\ExternalIdentities\ExternalIdentityDirectory;
use Avax\Components\Identity\Flows\ResolveExternalIdentity\ResolveExternalIdentity;

final readonly class ExternalIdentityGraph
{
    public function __construct(private ResolveExternalIdentity $resolveExternalIdentity, private ExternalIdentityDirectory $externalIdentities) {}

    public function resolveExternalIdentity(): ResolveExternalIdentity
    {
        return $this->resolveExternalIdentity;
    }

    public function externalIdentities(): ExternalIdentityDirectory
    {
        return $this->externalIdentities;
    }
}
