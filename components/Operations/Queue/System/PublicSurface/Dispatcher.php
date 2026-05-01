<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;
use DateTimeInterface;

final class Dispatcher
{
    private DispatchJob $dispatcher;

    private QueueBroker $broker;

    public function __construct(DispatchJob $dispatcher, QueueBroker $broker)
    {
        $this->dispatcher = $dispatcher;
        $this->broker     = $broker;
    }

    public function dispatch(JobDefinition $job): JobId
    {
        return $this->dispatcher->dispatch($job, $this->broker);
    }

    public function dispatchSync(JobDefinition $job): mixed
    {
        return $this->dispatcher->dispatchSync($job);
    }

    public function later(JobDefinition $job, DateTimeInterface $delay): JobId
    {
        return $this->dispatcher->later($job, $delay, $this->broker);
    }

    public function bulk(array $jobs): array
    {
        return $this->dispatcher->bulk($jobs, $this->broker);
    }
}

final class JobId
{
    public function __construct(
        public readonly string $value,
        public readonly ?string $queue = null,
    ) {
    }

    public static function generate(string $queue = null): self
    {
        return new self(
            value: uniqid('job-', true),
            queue: $queue,
        );
    }
}

final class JobResult
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $result = null,
        public readonly ?string $error = null,
        public readonly ?int $attempts = null,
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
