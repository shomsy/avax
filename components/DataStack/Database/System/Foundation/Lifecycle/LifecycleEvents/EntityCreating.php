<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;

/**
 * Event fired before an entity is inserted.
 *
 * Listeners may mutate $attributes before persistence.
 */
final readonly class EntityCreating
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $entityClass,
        public array $attributes,
        public string $connection,
        public string $phase = EntityLifecyclePhase::Creating->value,
    ) {
    }
}
