<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Configuration;

use Avax\Components\Operations\Concurrency\System\Capabilities\RunInCurrentProcess\CurrentProcessTaskRuntime;
use Avax\Components\Operations\Concurrency\System\Capabilities\RunWithFibers\FiberTaskRuntime;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Closure;
use Fiber;

interface TaskRuntimeInterface
{
    /**
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public function run(array $tasks, int|null $maxConcurrent = null) : ConcurrentResult;

    /**
     * @param list<Closure(): mixed> $tasks
     */
    public function race(array $tasks) : mixed;
}

final readonly class BuildConcurrencyRuntime
{
    public function build(?ConcurrencyConfig $config = null) : TaskRuntimeInterface
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
        return 'current_process';
    }
}
