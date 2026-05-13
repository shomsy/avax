<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Queue;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Closure;

/**
 * RegisterQueueCommands — provides CLI command closures for queue:work, queue:failed, queue:retry, queue:flush-failed.
 *
 * All dependencies are injected through the constructor.
 * No direct instantiation of brokers, stores, or worker loops.
 */
final readonly class RegisterQueueCommands
{
    /**
     * @param Closure(QueueBroker, Closure, int): mixed $createWorkerLoop
     */
    public function __construct(
        private QueueBroker            $broker,
        private FailedJobsStore        $failedStore,
        private ResolveCallable        $callableResolver,
        private Closure                $createWorkerLoop,
    ) {}

    /**
     * @return array<string, Closure>
     */
    public function __invoke(): array
    {
        return [
            'queue:work' => $this->queueWorkCommand(),
            'queue:failed' => $this->queueFailedCommand(),
            'queue:retry' => $this->queueRetryCommand(),
            'queue:flush-failed' => $this->queueFlushFailedCommand(),
        ];
    }

    private function queueWorkCommand(): Closure
    {
        return function (array $args): string {
            $output = "\033[33mQueue Worker\033[0m\n\n";

            $once = in_array('--once', $args, true);
            $queue = $this->argValue($args, '--queue', 'default');

            if ($once) {
                $output .= "Processing one job from queue: {$queue}\n\n";

                $worker = ($this->createWorkerLoop)(
                    $this->broker,
                    $this->resolveJobHandler(),
                    100_000,
                );

                $result = $worker->runOnce(queue: $queue, maxJobs: 1);
                $output .= sprintf(
                    "Processed: %d, Failed: %d, Dead-lettered: %d\n",
                    $result['processed'],
                    $result['failed'],
                    $result['dead_lettered'],
                );

                if ($result['processed'] === 0 && $result['failed'] === 0) {
                    $output .= "No jobs in queue.\n";
                }

                $output .= "\n\033[32mQueue worker completed.\033[0m\n";

                return $output;
            }

            $output .= "Queue worker running on queue: {$queue}\n";
            $output .= "Press Ctrl+C to stop.\n";
            $output .= "(Use --once for single job processing)\n";

            return $output;
        };
    }

    /**
     * Create a job handler closure that resolves handler classes through DI.
     */
    private function resolveJobHandler() : Closure
    {
        $resolver = $this->callableResolver;

        return static function (array $job) use ($resolver) : void {
            $handlerClass = $job['handler'] ?? null;
            if ($handlerClass === null) {
                return;
            }

            $handler = $resolver->resolve($handlerClass);
            if (method_exists($handler, 'handle')) {
                $handler->handle($job['payload'] ?? []);
            }
        };
    }

    private function queueFailedCommand(): Closure
    {
        return function (array $args): string {
            $output = "\033[33mFailed Queue Jobs\033[0m\n\n";

            $failed = $this->failedStore->list();

            if (empty($failed)) {
                $output .= "No failed jobs found.\n";

                return $output;
            }

            $output .= sprintf("%-6s %-15s %-20s %-50s\n", 'ID', 'Queue', 'Failed At', 'Exception');
            $output .= str_repeat('-', 95)."\n";

            foreach ($failed as $job) {
                $exception = strlen($job['exception']) > 47
                    ? substr($job['exception'], 0, 47).'...'
                    : $job['exception'];
                $output .= sprintf(
                    "%-6s %-15s %-20s %-50s\n",
                    $job['id'],
                    $job['queue'],
                    $job['failed_at'],
                    $exception,
                );
            }

            $output .= sprintf("\nTotal: %d failed job(s)\n", count($failed));

            return $output;
        };
    }

    private function queueRetryCommand(): Closure
    {
        return function (array $args): string {
            $output = "\033[33mRetry Failed Job\033[0m\n\n";

            $id = $this->argValue($args, '--id', '');

            if ($id === '') {
                $output .= "\033[31mUsage: queue:retry --id=<job_id>\033[0m\n";

                return $output;
            }

            $failed = $this->failedStore->find($id);

            if ($failed === null) {
                $output .= "\033[31mFailed job {$id} not found.\033[0m\n";

                return $output;
            }

            $payload = json_decode($failed['payload'], true, 512, JSON_THROW_ON_ERROR);
            $this->broker->push($failed['queue'], $payload);
            $this->failedStore->remove($id);

            $output .= "\033[32mJob {$id} retried on queue: {$failed['queue']}\033[0m\n";

            return $output;
        };
    }

    private function queueFlushFailedCommand(): Closure
    {
        return function (array $args): string {
            $output = "\033[33mFlush Failed Jobs\033[0m\n\n";

            $count = $this->failedStore->count();
            $this->failedStore->clear();

            $output .= "\033[32mFlushed {$count} failed job(s).\033[0m\n";

            return $output;
        };
    }

    /**
     * @param array<int, string> $args
     */
    private function argValue(array $args, string $name, string $default): string
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, $name.'=')) {
                return substr($arg, strlen($name) + 1);
            }
        }

        return $default;
    }
}
