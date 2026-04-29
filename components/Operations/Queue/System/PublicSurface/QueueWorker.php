<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\QueueDriverInterface;
use Avax\Components\Operations\Queue\System\Capabilities\Job;

final class QueueWorker
{
    private bool $shouldStop = false;

    public function __construct(
        private QueueDriverInterface $driver,
        private int $sleep = 3,
        private int $maxTries = 3,
    ) {}

    public function daemon(string $queue = 'default'): void
    {
        while (!$this->shouldStop) {
            $job = $this->driver->pop($queue);
            
            if ($job !== null) {
                $this->process($job);
            } else {
                sleep($this->sleep);
            }
        }
    }

    public function process(Job $job): void
    {
        try {
            $payload = $job->getPayload();
            $class = $payload['job'] ?? null;
            $data = $payload['data'] ?? [];
            
            if ($class && class_exists($class)) {
                $instance = new $class(...$data);
                $instance->handle();
                $job->delete();
            }
        } catch (\Throwable $e) {
            if ($job->attempts() >= $this->maxTries) {
                $job->delete();
            } else {
                $job->release();
            }
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }
}