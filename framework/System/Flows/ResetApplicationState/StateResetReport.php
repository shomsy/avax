<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ResetApplicationState;

final class StateResetReport
{
    /**
     * @param array<string, bool> $results
     */
    public function __construct(
        public readonly bool $success,
        public readonly int $componentsReset = 0,
        public readonly array $results = [],
    ) {
    }

    public function hasFailures(): bool
    {
        return in_array(false, $this->results, true);
    }

    public function failedComponents(): array
    {
        return array_keys($this->results, false);
    }
}
