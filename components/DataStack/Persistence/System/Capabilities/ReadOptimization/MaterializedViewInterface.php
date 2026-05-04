<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

interface MaterializedViewInterface
{
    public function name(): string;

    public function refresh(): MaterializedViewStats;

    public function read(array $filters = []): array;

    public function isStale(): bool;

    public function age(): float;

    public function lastRefreshedAt(): float;

    public function stalenessThreshold(): float;
}