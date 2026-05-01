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

final class DispatchJob
{
    private JobRegistry $registry;

    public function __construct(JobRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function dispatchSync(JobDefinition $job): JobResult
    {
        try {
            $handler = $this->registry->resolve($job->handler);
            $result = $handler($job->payload);

            return JobResult::success($result);
        } catch (Throwable $e) {
            return JobResult::failure($e->getMessage());
        }
    }

    public function later(JobDefinition $job, DateTimeInterface $delay, QueueBroker $broker): JobId
    {
        $queue = $job->queue ?? 'default';
        $jobId = JobId::generate($queue);

        $jobData       = $job->toArray();
        $jobData['id'] = $jobId->value;
        $jobData['executeAt'] = $delay->getTimestamp();

        $broker->push($queue, $jobData);

        return $jobId;
    }

    public function bulk(array $jobs, QueueBroker $broker): array
    {
        $ids = [];

        foreach ($jobs as $job) {
            if (! $job instanceof JobDefinition) {
                continue;
            }

            $ids[] = $this->dispatch($job, $broker);
        }

        return $ids;
    }

    public function dispatch(JobDefinition $job, QueueBroker $broker): JobId
    {
        $queue = $job->queue ?? 'default';
        $jobId = JobId::generate($queue);

        $jobData = $job->toArray();
        $jobData['id'] = $jobId->value;

        $broker->push($queue, $jobData);

        return $jobId;
    }
}
