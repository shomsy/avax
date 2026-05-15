<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Configuration\Builders;

use Avax\Components\Operations\Concurrency\System\Configuration\TaskRuntimeInterface;

use Avax\Components\Operations\Concurrency\System\Configuration\ConcurrencyConfig;

use Avax\Components\Operations\Concurrency\System\Capabilities\RunInCurrentProcess\CurrentProcessTaskRuntime;
use Avax\Components\Operations\Concurrency\System\Capabilities\RunWithFibers\FiberTaskRuntime;
use Fiber;

final readonly class BuildConcurrencyRuntime
{
    public function build(ConcurrencyConfig|null $config = null) : TaskRuntimeInterface
    {
        $config = $config ?? ConcurrencyConfig::fromArray([]);

        return match ($config->runtime) {
            'fiber' => $this->buildFiberRuntime(),
            default => $this->buildCurrentProcessRuntime(),
        };
    }

    private function buildFiberRuntime() : FiberTaskRuntime
    {
        return new FiberTaskRuntime();
    }

    private function buildCurrentProcessRuntime() : CurrentProcessTaskRuntime
    {
        return new CurrentProcessTaskRuntime();
    }

    /**
     * @return array<string, bool>
     */
    public function detectAvailableRuntimes() : array
    {
        $runtimes = ['current_process' => true];

        if (class_exists(Fiber::class)) {
            $runtimes['fiber'] = true;
        }

        return $runtimes;
    }

    public function getDefaultRuntime() : string
    {
        return class_exists(Fiber::class)
            ? 'fiber'
            : 'current_process';
    }
}
