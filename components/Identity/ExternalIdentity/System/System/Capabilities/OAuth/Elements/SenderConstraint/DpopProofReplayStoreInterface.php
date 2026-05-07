<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\SenderConstraint;

use DateTimeImmutable;

/**
 * Stores seen DPoP proof ids to reject replay.
 */
interface DpopProofReplayStoreInterface
{
    public function remember(string $proofId, DateTimeImmutable $expiresAt) : bool;
}
