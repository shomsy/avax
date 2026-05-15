<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Configuration\Builders;

use Avax\Components\Operations\Parallelism\System\Capabilities\RunInCurrentProcess\CurrentProcessParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool\ReadWorkerResult;
use Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool\StartWorkerProcess;
use Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool\StopWorkerProcess;
use Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool\SymfonyProcessParallelRuntime;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelismConfig;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelRuntimeInterface;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Symfony\Component\Process\Process;

final readonly class BuildParallelRuntime
{
    public function build(ParallelismConfig|null $config = null) : ParallelRuntimeInterface
    {
        $config = $config ?? ParallelismConfig::fromArray([]);

        return match ($config->runtime) {
            'process', 'symfony_process' => $this->buildSymfonyProcessRuntime(),
            default                      => $this->buildCurrentProcessRuntime(),
        };
    }

    private function buildSymfonyProcessRuntime() : SymfonyProcessParallelRuntime
    {
        return new SymfonyProcessParallelRuntime(
            starter: new StartWorkerProcess(),
            reader : new ReadWorkerResult(),
            stopper: new StopWorkerProcess(),
        );
    }

    private function buildCurrentProcessRuntime() : CurrentProcessParallelRuntime
    {
        return new CurrentProcessParallelRuntime();
    }

    public function getDefaultRuntime() : string
    {
        $available = $this->detectAvailableRuntimes();

        if (isset($available['symfony_process'])) {
            return 'symfony_process';
        }

        return 'current_process';
    }

    /**
     * @return array<string, bool>
     */
    public function detectAvailableRuntimes() : array
    {
        $runtimes = ['current_process' => true];

        if (class_exists(Process::class)) {
            $runtimes['symfony_process'] = true;
        }

        if (function_exists('pcntl_fork')) {
            $runtimes['process_fork'] = true;
        }

        return $runtimes;
    }
}