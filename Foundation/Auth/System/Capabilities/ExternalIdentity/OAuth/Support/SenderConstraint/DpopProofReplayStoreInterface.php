<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\OAuth\SenderConstraint;

use DateTimeImmutable;

/**
 * Stores seen DPoP proof ids to reject replay.
 */
interface DpopProofReplayStoreInterface
{
    public function remember(string $proofId, DateTimeImmutable $expiresAt) : bool;
}
