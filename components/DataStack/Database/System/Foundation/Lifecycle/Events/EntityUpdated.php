<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;

/**
 * Event fired after an entity is updated successfully.
 */
final readonly class EntityUpdated
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $entityClass,
        public object $entity,
        public array $attributes,
        public string $connection,
        public string $phase = EntityLifecyclePhase::Updated->value,
    ) {
    }
}
