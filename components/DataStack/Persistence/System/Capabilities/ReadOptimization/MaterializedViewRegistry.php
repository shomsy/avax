<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

use RuntimeException;

final class MaterializedViewRegistry
{
    private array $views = [];

    public function register(MaterializedView $materializedView): void
    {
        $this->views[$materializedView->name()] = $materializedView;
    }

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

    public function get(string $name): MaterializedView
    {
        if (! isset($this->views[$name])) {
            throw new RuntimeException(sprintf("Materialized view '%s' not found", $name));
        }

        return $this->views[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->views[$name]);
    }

    public function list(): array
    {
        return array_keys($this->views);
    }
}
