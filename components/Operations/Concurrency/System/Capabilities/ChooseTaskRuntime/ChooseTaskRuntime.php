<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\ChooseTaskRuntime;

use Avax\Components\Operations\Concurrency\System\Configuration\Builders\BuildConcurrencyRuntime;
use Avax\Components\Operations\Concurrency\System\Configuration\ConcurrencyConfig;
use Avax\Components\Operations\Concurrency\System\Configuration\TaskRuntimeInterface;

final readonly class ChooseTaskRuntime
{
    public function __construct(
        private BuildConcurrencyRuntime $builder,
    ) {}

    public function forConfig(ConcurrencyConfig|null $config = null) : TaskRuntimeInterface
    {
        $config = $config ?? ConcurrencyConfig::fromArray([]);

        return $this->builder->build($config);
    }

    public function forRuntime(string $runtime) : TaskRuntimeInterface
    {
        $config = new ConcurrencyConfig(runtime: $runtime);

        return $this->builder->build($config);
    }

    /**
     * @return array<string, bool>
     */
    public function availableRuntimes() : array
    {
        return $this->builder->detectAvailableRuntimes();
    }
}
