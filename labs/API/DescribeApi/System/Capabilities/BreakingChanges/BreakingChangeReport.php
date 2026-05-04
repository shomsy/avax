<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges;

final class BreakingChangeReport
{
    /**
     * @param list<BreakingChange> $changes
     */
    public function __construct(
        public readonly array $changes = [],
    )
    {
    }

    public function hasBreakingChanges(): bool
    {
        foreach ($this->changes as $change) {
            if ($change->severity() === 'critical') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<BreakingChange>
     */
    public function criticalChanges(): array
    {
        return array_values(
            array_filter(
                $this->changes,
                static fn(BreakingChange $change): bool => $change->severity() === 'critical',
            ),
        );
    }

    public function count(): int
    {
        return count($this->changes);
    }
}
