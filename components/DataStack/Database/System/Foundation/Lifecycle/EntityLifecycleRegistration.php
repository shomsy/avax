<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Registration record for an entity lifecycle listener.
 *
 * Created by the onEntity() DSL or by compile-time attribute scanning.
 * Stores the entity class, lifecycle phase, listener class-string,
 * priority, source, and execution mode.
 */
final readonly class EntityLifecycleRegistration
{
    public function __construct(
        public string $entityClass,
        public EntityLifecyclePhase $phase,
        public string $listener,
        public int $priority = 0,
        public LifecycleSource $source = LifecycleSource::Dsl,
        public LifecycleExecutionMode $mode = LifecycleExecutionMode::Sync,
    ) {
    }
}
