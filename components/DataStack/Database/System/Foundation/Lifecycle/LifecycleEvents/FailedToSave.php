<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;
use Throwable;

/**
 * Event fired when entity insert or update fails.
 *
 * Exception still bubbles — this event reports, it does not catch.
 */
final readonly class FailedToSave
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $entityClass,
        public array $attributes,
        public string $connection,
        public Throwable $exception,
        public string $phase = EntityLifecyclePhase::FailedToSave->value,
    ) {
    }
}
