<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;

/**
 * Event fired after an entity is inserted successfully.
 *
 * Receives the persisted entity instance with generated identifier.
 */
final readonly class EntityCreated
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $entityClass,
        public object $entity,
        public array $attributes,
        public string $connection,
        public string $lastInsertId = '',
        public string $phase = EntityLifecyclePhase::Created->value,
    ) {
    }
}
