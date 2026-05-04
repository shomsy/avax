<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Budget;

class MemoryBudget
{
    private int $used = 0;

    private int $peak = 0;

    public function __construct(private readonly int $limit)
    {
    }

    public function allocate(int $bytes): bool
    {
        if ($this->used + $bytes > $this->limit) {
            return false;
        }

        $this->used += $bytes;
        if ($this->used > $this->peak) {
            $this->peak = $this->used;
        }

        return true;
    }

    public function release(int $bytes): void
    {
        $this->used = max(0, $this->used - $bytes);
    }

    public function usage(): float
    {
        return $this->limit > 0 ? $this->used / $this->limit : 0;
    }

    public function isExhausted(): bool
    {
        return $this->used >= $this->limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getUsed(): int
    {
        return $this->used;
    }

    public function getPeak(): int
    {
        return $this->peak;
    }
}