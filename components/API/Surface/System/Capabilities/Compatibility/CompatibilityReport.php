<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Compatibility;

final class CompatibilityReport
{
    /**
     * @param list<CompatibilityChange> $changes
     */
    public function __construct(
        public readonly array $changes = [],
    ) {}

    public function hasCompatibilityIssues() : bool
    {
        foreach ($this->changes as $change) {
            if ($change->severity() === 'critical') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<CompatibilityChange>
     */
    public function criticalChanges() : array
    {
        return array_values(
            array_filter(
                $this->changes,
                static fn (CompatibilityChange $change) : bool => $change->severity() === 'critical',
            ),
        );
    }

    public function count() : int
    {
        return count($this->changes);
    }
}
