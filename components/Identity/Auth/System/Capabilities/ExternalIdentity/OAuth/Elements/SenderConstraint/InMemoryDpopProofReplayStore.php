<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint;

use DateTimeImmutable;

/**
 * In-memory replay store for DPoP proof ids.
 */
final class InMemoryDpopProofReplayStore implements DpopProofReplayStoreInterface
{
    /** @var array<string, DateTimeImmutable> */
    private array $proofs = [];

    public function remember(string $proofId, DateTimeImmutable $expiresAt) : bool
    {
        $now = new DateTimeImmutable();

        foreach ($this->proofs as $storedId => $storedExpiry) {
            if ($storedExpiry <= $now) {
                unset($this->proofs[$storedId]);
            }
        }

        if (isset($this->proofs[$proofId])) {
            return false;
        }

        $this->proofs[$proofId] = $expiresAt;

        return true;
    }
}
