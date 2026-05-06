<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Flows\Dispatch;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\PublicSurface\JobId;
use Avax\Components\Operations\Queue\System\PublicSurface\JobResult;
use DateTimeInterface;
use Throwable;

final readonly class DispatchJob
{
    public function __construct(private JobRegistry $jobRegistry)
    {
    }

    public function dispatchSync(JobDefinition $jobDefinition): JobResult
    {
        try {
            $handler = $this->jobRegistry->resolve($jobDefinition->handler);
            $result = $handler($jobDefinition->payload);

            return JobResult::success($result);
        } catch (Throwable $throwable) {
            return JobResult::failure($throwable->getMessage());
        }
    }

    public function later(JobDefinition $jobDefinition, DateTimeInterface $delay, QueueBroker $queueBroker): JobId
    {
        $queue = $jobDefinition->queue ?? 'default';
        $jobId = JobId::generate($queue);

        $jobData = $jobDefinition->toArray();
        $jobData['id'] = $jobId->value;
        $jobData['executeAt'] = $delay->getTimestamp();

        $queueBroker->push($queue, $jobData);

        return $jobId;
    }

    public function bulk(array $jobs, QueueBroker $queueBroker): array
    {
        $ids = [];

        foreach ($jobs as $job) {
            if (! $job instanceof JobDefinition) {
                continue;
            }

            $ids[] = $this->dispatch($job, $queueBroker);
        }

        return $ids;
    }

    public function dispatch(JobDefinition $jobDefinition, QueueBroker $queueBroker): JobId
    {
        $queue = $jobDefinition->queue ?? 'default';
        $jobId = JobId::generate($queue);

        $jobData = $jobDefinition->toArray();
        $jobData['id'] = $jobId->value;

        $queueBroker->push($queue, $jobData);

        return $jobId;
    }
}
