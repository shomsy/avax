<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM;

abstract class Relation
{
    public function __construct(
        protected string $parent,
        protected string $related,
        protected string $foreignKey,
        protected string $localKey,
    ) {}

    abstract public function getResults() : mixed;
}

final class HasMany extends Relation
{
    public function getResults() : array
    {
        // Placeholder for ORM logic
        return [];
    }
}
