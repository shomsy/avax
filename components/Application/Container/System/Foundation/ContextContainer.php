<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Foundation;

use Psr\Container\ContainerInterface;

/**
 * Context container holding scoped service instances.
 *
 * Provides per-context service resolution layered on top of a base container.
 * Used by DIContainer::forContext() and slice views.
 */
final readonly class ContextContainer implements ContainerInterface
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        private ContainerInterface $base,
        private object $resolver,
        private array $context = [],
    ) {
    }

    public function get(string $id): mixed
    {
        return $this->base->get($id);
    }

    public function has(string $id): bool
    {
        return $this->base->has($id);
    }
}
