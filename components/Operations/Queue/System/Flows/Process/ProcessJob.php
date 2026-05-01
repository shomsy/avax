<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Flows\Process;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\PublicSurface\JobResult;
use Throwable;

final readonly class ProcessJob
{
    public function __construct(private JobRegistry $jobRegistry)
    {
    }

    public function processAll(QueueBroker $queueBroker, string $queue = 'default') : array
    {
        return $this->processQueue($queue, $queueBroker, 100);
    }

    public function processQueue(string $queue, QueueBroker $queueBroker, int $limit = 10) : array
    {
        $results = [];

        for ($i = 0; $i < $limit; $i++) {
            $jobData = $queueBroker->pop($queue);

            if ($jobData === null) {
                break;
            }

            $executeAt = $jobData['executeAt'] ?? null;
            if ($executeAt !== null && $executeAt > time()) {
                $queueBroker->push($queue, $jobData);

                break;
            }

            $results[] = $this->process($jobData, $queueBroker);
        }

        return $results;
    }

    public function process(array $jobData, QueueBroker $queueBroker) : JobResult
    {
        $jobDefinition                = JobDefinition::fromArray($jobData);
        $attempts = ($jobData['attempts'] ?? 0) + 1;

        if ($attempts > $jobDefinition->maxAttempts) {
            $queueBroker->remove($jobDefinition->queue ?? 'default', $jobData['id']);

            return JobResult::failure('Max attempts exceeded', $attempts);
        }

        try {
            $handler = $this->jobRegistry->resolve($jobDefinition->handler);
            $result  = $handler($jobDefinition->payload);

            $queueBroker->remove($jobDefinition->queue ?? 'default', $jobData['id']);

            return JobResult::success($result, $attempts);
        } catch (Throwable $throwable) {
            if ($jobDefinition->retryDelay > 0 && $attempts < $jobDefinition->maxAttempts) {
                $jobData['attempts'] = $attempts;
                $jobData['executeAt'] = time() + $jobDefinition->retryDelay;
                $queueBroker->push($jobDefinition->queue ?? 'default', $jobData);
            }

            return JobResult::failure($throwable->getMessage(), $attempts);
        }
    }
}
