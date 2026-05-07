<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

use RuntimeException;

final class MaterializedViewRegistry
{
    /**
     * @var array<string, MaterializedView>
     */
    private array $views = [];

    public function register(MaterializedView $materializedView): void
    {
        $this->views[$materializedView->name()] = $materializedView;
    }

    /**
     * @return array<string, MaterializedViewStats>
     */
    public function refreshAll(): array
    {
        $results = [];

        foreach ($this->views as $name => $view) {
            $results[$name] = $view->refresh();
        }

        return $results;
    }

    public function refresh(string $name): MaterializedViewStats
    {
        return $this->get($name)->refresh();
    }

    /**
     * @throws RuntimeException If the view is not found
     */
    public function get(string $name): MaterializedView
    {
        if (! isset($this->views[$name])) {
            throw new RuntimeException(sprintf("Materialized view '%s' is not registered", $name));
        }

        return $this->views[$name];
    }

    /**
     * @return list<MaterializedView>
     */
    public function staleViews() : array
    {
        return array_values(array_filter(
                                $this->views,
                                static fn (MaterializedView $materializedView) : bool => $materializedView->isStale(),
                            ));
    }

    /**
     * @return list<string>
     */
    public function viewNames() : array
    {
        return array_keys($this->views);
    }

    public function has(string $name): bool
    {
        return isset($this->views[$name]);
    }

    public function remove(string $name) : void
    {
        unset($this->views[$name]);
    }

    public function count() : int
    {
        return count($this->views);
    }

    public function clear() : void
    {
        $this->views = [];
    }
}
