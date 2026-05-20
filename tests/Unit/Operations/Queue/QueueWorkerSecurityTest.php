<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Job;
use Avax\Components\Operations\Queue\System\Capabilities\QueueDriverInterface;
use Avax\Components\Operations\Queue\System\Foundation\JobInterface;
use Avax\Components\Operations\Queue\System\PublicSurface\QueueWorker;
use PHPUnit\Framework\TestCase;

final class QueueWorkerSecurityTest extends TestCase
{
    public function testRejectsJobClassNotImplementingJobInterface(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $worker = new QueueWorker($driver);
        $job = new QueueWorkerInvalidJob();

        $worker->process($job);

        self::assertTrue($job->isDeleted());
    }

    public function testRejectsNonExistentJobClassAndDeletesJob(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $worker = new QueueWorker($driver);
        $job = new QueueWorkerNonExistentJob();

        $worker->process($job);

        self::assertTrue($job->isDeleted());
    }

    public function testAcceptsValidJobClassImplementingJobInterface(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $worker = new QueueWorker($driver);
        $job = new QueueWorkerValidJob();

        $worker->process($job);

        self::assertTrue($job->isDeleted());
    }
}

final class QueueWorkerInvalidJob implements Job
{
    private bool $deleted = false;

    public function getId(): string
    {
        return 'invalid-job';
    }

    public function getPayload(): array
    {
        return ['job' => self::class, 'data' => []];
    }

    public function attempts(): int
    {
        return 0;
    }

    public function release(int $delay = 0): void
    {
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}

final class QueueWorkerNonExistentJob implements Job
{
    private bool $deleted = false;

    public function getId(): string
    {
        return 'non-existent-job';
    }

    public function getPayload(): array
    {
        return ['job' => 'NonExistentClass_XYZ', 'data' => []];
    }

    public function attempts(): int
    {
        return 0;
    }

    public function release(int $delay = 0): void
    {
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}

final class QueueWorkerValidJob implements Job, JobInterface
{
    private bool $deleted = false;

    public function getId(): string
    {
        return 'valid-job';
    }

    public function getPayload(): array
    {
        return ['job' => self::class, 'data' => []];
    }

    public function attempts(): int
    {
        return 0;
    }

    public function release(int $delay = 0): void
    {
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function handle(array $data = []): void
    {
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}
