<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;

/**
 * Event fired before an entity is inserted or updated.
 *
 * Superset of Creating and Updating. Listeners may mutate attributes.
 */
final readonly class EntitySaving
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $entityClass,
        public object|null $entity,
        public array $attributes,
        public string $connection,
        public string $phase = EntityLifecyclePhase::Saving->value,
    ) {
    }
}
