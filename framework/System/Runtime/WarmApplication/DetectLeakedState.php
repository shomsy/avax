<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\WarmApplication;

/**
 * DetectLeakedState — Detects if state leaked between requests.
 */
final class DetectLeakedState
{
    private ?string $snapshotBefore = null;
    private ?string $snapshotAfter = null;

    public function captureBefore(string $identifier): void
    {
        $this->snapshotBefore = $identifier;
    }

    public function captureAfter(string $identifier): void
    {
        $this->snapshotAfter = $identifier;
    }

    public function hasLeak(): bool
    {
        return $this->snapshotBefore !== $this->snapshotAfter && $this->snapshotBefore !== null;
    }

    public function clear(): void
    {
        $this->snapshotBefore = null;
        $this->snapshotAfter = null;
    }
}
