<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Flows\Process;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\PublicSurface\JobResult;
use Throwable;

final class ProcessJob
{
    private JobRegistry $registry;

    public function __construct(JobRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function processAll(QueueBroker $broker, string $queue = 'default') : array
    {
        return $this->processQueue($queue, $broker, 100);
    }

    public function processQueue(string $queue, QueueBroker $broker, int $limit = 10) : array
    {
        $results = [];

        for ($i = 0; $i < $limit; $i++) {
            $jobData = $broker->pop($queue);

            if ($jobData === null) {
                break;
            }

            $executeAt = $jobData['executeAt'] ?? null;
            if ($executeAt !== null && $executeAt > time()) {
                $broker->push($queue, $jobData);

                break;
            }

            $results[] = $this->process($jobData, $broker);
        }

        return $results;
    }

    public function process(array $jobData, QueueBroker $broker) : JobResult
    {
        $job      = JobDefinition::fromArray($jobData);
        $attempts = ($jobData['attempts'] ?? 0) + 1;

        if ($attempts > $job->maxAttempts) {
            $broker->remove($job->queue ?? 'default', $jobData['id']);

            return JobResult::failure('Max attempts exceeded', $attempts);
        }

        try {
            $handler = $this->registry->resolve($job->handler);
            $result  = $handler($job->payload);

            $broker->remove($job->queue ?? 'default', $jobData['id']);

            return JobResult::success($result, $attempts);
        } catch (Throwable $e) {
            if ($job->retryDelay > 0 && $attempts < $job->maxAttempts) {
                $jobData['attempts']  = $attempts;
                $jobData['executeAt'] = time() + $job->retryDelay;
                $broker->push($job->queue ?? 'default', $jobData);
            }

            return JobResult::failure($e->getMessage(), $attempts);
        }
    }
}
