<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;
use Throwable;

/**
 * Event fired when entity delete fails.
 *
 * Exception still bubbles — this event reports, it does not catch.
 */
final readonly class FailedToDelete
{
    public function __construct(
        public string $entityClass,
        public object $entity,
        public string $connection,
        public Throwable $exception,
        public string $phase = EntityLifecyclePhase::FailedToDelete->value,
    ) {
    }
}
