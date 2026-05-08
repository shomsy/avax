<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool;

use Avax\Components\Operations\Parallelism\System\Foundation\ParallelFailure;
use Avax\Components\Operations\Parallelism\System\Foundation\WorkerResult;
use Symfony\Component\Process\Process;

final readonly class ReadWorkerResult
{
    public function read(Process $process, string $workerId) : WorkerResult
    {
        $process->wait();

        $output      = $process->getOutput();
        $errorOutput = $process->getErrorOutput();

        if ($process->isSuccessful()) {
            $result = $this->parseOutput($output);

            return WorkerResult::success(
                workerId: $workerId,
                value   : $result['value'] ?? null,
            );
        }

        $exitCode     = $process->getExitCode() ?? 1;
        $errorMessage = trim($errorOutput) ?: trim($output) ?: 'Worker process failed';

        return WorkerResult::failure(
            workerId: $workerId,
            failure : new ParallelFailure(
                          name   : $workerId,
                          message: $errorMessage,
                          code   : $exitCode,
                      ),
        );
    }

    public function getOutput(Process $process) : string
    {
        return $process->getOutput();
    }

    /**
     * @return array<string, mixed>
     */
    private function parseOutput(string $output) : array
    {
        $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            return ['value' => $decoded];
        }

        return $decoded;
    }

    public function isComplete(Process $process) : bool
    {
        return ! $process->isRunning();
    }
}
