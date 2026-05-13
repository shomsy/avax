<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;

/**
 * Event fired after an entity is inserted or updated successfully.
 *
 * Superset of Created and Updated.
 */
final readonly class EntitySaved
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $entityClass,
        public object $entity,
        public array $attributes,
        public string $connection,
        public string $phase = EntityLifecyclePhase::Saved->value,
    ) {
    }
}
