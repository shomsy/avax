<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;
use DateTimeInterface;

final readonly class Dispatcher
{
    public function __construct(private DispatchJob $dispatchJob, private QueueBroker $queueBroker)
    {
    }

    public function dispatch(JobDefinition $jobDefinition): JobId
    {
        return $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);
    }

    public function dispatchSync(JobDefinition $jobDefinition): mixed
    {
        return $this->dispatchJob->dispatchSync($jobDefinition);
    }

    public function later(JobDefinition $jobDefinition, DateTimeInterface $delay): JobId
    {
        return $this->dispatchJob->later($jobDefinition, $delay, $this->queueBroker);
    }

    public function bulk(array $jobs): array
    {
        return $this->dispatchJob->bulk($jobs, $this->queueBroker);
    }
}
