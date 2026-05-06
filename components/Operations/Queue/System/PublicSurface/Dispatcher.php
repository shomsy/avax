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

final readonly class JobId
{
    public function __construct(
        public string $value,
        public ?string $queue = null,
    ) {
    }

    public static function generate(?string $queue = null): self
    {
        return new self(
            value: uniqid('job-', true),
            queue: $queue,
        );
    }
}

final readonly class JobResult
{
    public function __construct(
        public bool $success,
        public mixed $result = null,
        public ?string $error = null,
        public ?int $attempts = null,
    ) {
    }

    public static function success(mixed $result = null, int $attempts = 1): self
    {
        return new self(success: true, result: $result, attempts: $attempts);
    }

    public static function failure(string $error, int $attempts = 1): self
    {
        return new self(success: false, error: $error, attempts: $attempts);
    }
}
