<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

use DateTimeImmutable;

/**
 * Resolved lifecycle source with state.
 */
final readonly class ResolvedLifecycleSource
{
    public function __construct(
        public Source $source,
        public LifecycleState $state,
        public DateTimeImmutable $resolvedAt
    ) {}
}