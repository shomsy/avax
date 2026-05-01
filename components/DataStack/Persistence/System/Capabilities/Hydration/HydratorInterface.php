<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Hydration;

/**
 * Contract for hydrating database rows into entity objects.
 */
interface HydratorInterface
{
    /**
     * Hydrate a single database row into an entity object.
     *
     * @param class-string         $entityClass
     * @param array<string, mixed> $row
     */
    public function hydrate(string $entityClass, array $row): object;

    /**
     * Hydrate multiple database rows into entity objects.
     *
     * @param class-string                     $entityClass
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<object>
     */
    public function hydrateAll(string $entityClass, array $rows): array;
}
