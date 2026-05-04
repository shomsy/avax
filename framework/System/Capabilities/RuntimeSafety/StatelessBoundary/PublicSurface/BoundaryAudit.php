<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety\StatelessBoundary\PublicSurface;

use Avax\Framework\System\Capabilities\RuntimeSafety\StatelessBoundary\Capabilities\Enforcement\StatelessGuard;

final readonly class BoundaryAudit
{
    /**
     * @param list<string> $statelessRoutes
     * @param list<string> $statefulViolations
     */
    public function __construct(
        public string $mode,
        public array  $statelessRoutes,
        public array  $statefulViolations,
    )
    {
    }

    public function isCompliant(): bool
    {
        return $this->statefulViolations === [];
    }
}
