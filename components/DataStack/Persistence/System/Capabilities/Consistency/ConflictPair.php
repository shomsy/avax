<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * A pair of conflicting values awaiting manual resolution.
 */
final readonly class ConflictPair
{
    public function __construct(
        public mixed $valueA,
        public mixed $valueB,
        public array $context = [],
        public float $createdAt = 0.0,
    )
    {
    }

    /**
     * Resolves the conflict by choosing value A.
     */
    public function chooseA(): mixed
    {
        return $this->valueA;
    }

    /**
     * Resolves the conflict by choosing value B.
     */
    public function chooseB(): mixed
    {
        return $this->valueB;
    }

    /**
     * Returns a summary string.
     */
    public function summary(): string
    {
        return sprintf(
            "Conflict (age: %.1fs):\n  A: %s\n  B: %s",
            $this->age(),
            var_export($this->valueA, true),
            var_export($this->valueB, true),
        );
    }

    /**
     * Returns the age of the conflict in seconds.
     */
    public function age(): float
    {
        return microtime(true) - $this->createdAt;
    }
}
