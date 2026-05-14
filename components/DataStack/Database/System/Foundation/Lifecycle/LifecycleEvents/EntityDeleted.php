<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;

/**
 * Event fired after an entity is deleted successfully.
 */
final readonly class EntityDeleted
{
    public function __construct(
        public string $entityClass,
        public object $entity,
        public string $connection,
        public string $phase = EntityLifecyclePhase::Deleted->value,
    ) {
    }
}
