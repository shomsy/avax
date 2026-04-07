<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

final class BlueprintCache
{
    /** @var array<string, ServiceBlueprint> */
    private array $items = [];

    public function get(string $class) : ServiceBlueprint|null
    {
        return $this->items[$class] ?? null;
    }

    public function put(ServiceBlueprint $blueprint) : ServiceBlueprint
    {
        $this->items[$blueprint->class] = $blueprint;

        return $blueprint;
    }
}
