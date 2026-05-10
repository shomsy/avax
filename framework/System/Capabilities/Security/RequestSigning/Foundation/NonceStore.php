<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation;

final class NonceStore
{
    /** @var array<string, int> */
    private array $nonces = [];

    public function has(string $nonce): bool
    {
        return isset($this->nonces[$nonce]);
    }

    public function store(string $nonce, int $timestamp): void
    {
        $this->nonces[$nonce] = $timestamp;
    }

    public function pruneOlderThan(int $cutoffEpoch): void
    {
        foreach ($this->nonces as $storedNonce => $timestamp) {
            if ($timestamp < $cutoffEpoch) {
                unset($this->nonces[$storedNonce]);
            }
        }
    }

    public function clear(): void
    {
        $this->nonces = [];
    }
}
