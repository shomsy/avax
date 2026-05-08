<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool;

use Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload\RejectUnserializableWork;
use Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload\SerializeWorkPayload;
use Avax\Components\Operations\Parallelism\System\Configuration\ParallelRuntimeInterface;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelFailure;
use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;
use Avax\Components\Operations\Parallelism\System\Foundation\WorkerId;
use Avax\Components\Operations\Parallelism\System\Foundation\WorkerResult;
use Closure;
use Symfony\Component\Process\Process;
use Throwable;

final readonly class SymfonyProcessParallelRuntime implements ParallelRuntimeInterface
{
    public function __construct(
        private SerializeWorkPayload     $serializer = new SerializeWorkPayload(),
        private StartWorkerProcess       $starter = new StartWorkerProcess(),
        private ReadWorkerResult         $reader = new ReadWorkerResult(),
        private StopWorkerProcess        $stopper = new StopWorkerProcess(),
        private RejectUnserializableWork $rejector = new RejectUnserializableWork(),
    ) {}

    /**
     * @param array<string|int, Closure(): mixed> $work
     */
    public function run(array $work, int|null $maxWorkers = null, ?string $workerScript = null) : ParallelResult
    {
        if (empty($work)) {
            return new ParallelResult(
                values         : [],
                failures       : [],
                startedWorkers : 0,
                finishedWorkers: 0,
                failedWorkers  : 0,
            );
        }

        $validatedWork = $this->rejector->filter($work);
        $maxWorkers    = $maxWorkers ?? count($validatedWork);

        $values          = [];
        $failures        = [];
        $startedWorkers  = 0;
        $finishedWorkers = 0;
        $failedWorkers   = 0;

        $batch     = [];
        $batchSize = min($maxWorkers, count($validatedWork));

        foreach ($validatedWork as $name => $action) {
            $workerId   = WorkerId::generate()->toString();
            $serialized = $this->serializer->serialize((string) $name, $action);

            try {
                $process          = $this->starter->start($serialized, $workerScript);
                $batch[$workerId] = [
                    'name'    => $name,
                    'process' => $process,
                ];
                $startedWorkers++;

                if (count($batch) >= $batchSize) {
                    $results = $this->waitForBatch($batch);
                    [$batchValues, $batchFailures] = $this->processBatchResults($results, $batch);
                    $values          = array_merge($values, $batchValues);
                    $failures        = array_merge($failures, $batchFailures);
                    $finishedWorkers += count($batch);
                    $failedWorkers   += count($batchFailures);
                    $batch           = [];
                }
            } catch (Throwable $e) {
                $failures[] = new ParallelFailure(
                    name    : $name,
                    message : 'Failed to start worker: ' . $e->getMessage(),
                    code    : $e->getCode(),
                    previous: $e,
                );
                $failedWorkers++;
                $startedWorkers++;
                $finishedWorkers++;
            }
        }

        if (count($batch) > 0) {
            $results = $this->waitForBatch($batch);
            [$batchValues, $batchFailures] = $this->processBatchResults($results, $batch);
            $values          = array_merge($values, $batchValues);
            $failures        = array_merge($failures, $batchFailures);
            $finishedWorkers += count($batch);
            $failedWorkers   += count($batchFailures);
        }

        return new ParallelResult(
            values         : $values,
            failures       : $failures,
            startedWorkers : $startedWorkers,
            finishedWorkers: $finishedWorkers,
            failedWorkers  : $failedWorkers,
        );
    }

    /**
     * @param array<string, array{name: string|int, process: Process}> $batch
     *
     * @return array<string, WorkerResult>
     */
    private function waitForBatch(array $batch) : array
    {
        $results = [];

        foreach ($batch as $workerId => $item) {
            $process = $item['process'];
            $process->wait();
            $results[$workerId] = $this->reader->read($process, $workerId);
        }

        return $results;
    }

    /**
     * @param array<string, WorkerResult> $results
     * @param array<string, array{name: string|int, process: Process}>   $batch
     *
     * @return array{array<string|int, mixed>, list<ParallelFailure>}
     */
    private function processBatchResults(array $results, array $batch) : array
    {
        $values   = [];
        $failures = [];

        foreach ($results as $workerId => $result) {
            $name = $batch[$workerId]['name'] ?? $workerId;

            if ($result->isSuccess()) {
                $values[$name] = $result->value;
            } else {
                $failure    = $result->failure ?? new ParallelFailure(
                    name   : $name,
                    message: 'Unknown worker failure',
                    code   : 1,
                );
                $failures[] = $failure;
            }

            $this->stopper->stop($batch[$workerId]['process']);
        }

        return [$values, $failures];
    }
}
