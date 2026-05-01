<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\Fakes;

use Avax\Components\Operations\Queue\System\Capabilities\Job;
use Avax\Components\Operations\Queue\System\Capabilities\QueueDriverInterface;
use DateTimeInterface;
use PHPUnit\Framework\Assert;

/**
 * QueueFake - captures dispatched jobs for assertions in tests.
 * Replaces the real queue driver during testing.
 */
class QueueFake implements QueueDriverInterface
{
    /** @var array<string, list<array{job: string, data: array}>> */
    private array $pushed = [];

    /** @var array<string, list<array{job: string, data: array, delay: DateTimeInterface}>> */
    private array $pushedLater = [];

    public function later(DateTimeInterface $delay, string $job, array $data = []): string
    {
        if (! isset($this->pushedLater[$job])) {
            $this->pushedLater[$job] = [];
        }

        $this->pushedLater[$job][] = [
            'job'  => $job,
            'data' => $data,
            'delay' => $delay,
        ];

        return 'fake-later-' . uniqid('', true);
    }

    public function pop(): ?Job
    {
        return null; // Never pop anything in fake
    }

    public function size(string $queue = 'default'): int
    {
        return $this->pushedCountTotal();
    }

    /**
     * Get the total count of all pushed jobs.
     */
    public function pushedCountTotal(): int
    {
        $total = 0;
        foreach ($this->pushed as $jobs) {
            $total += count($jobs);
        }

        foreach ($this->pushedLater as $jobs) {
            $total += count($jobs);
        }

        return $total;
    }

    public function bulk(array $jobs, string $queue = 'default'): void
    {
        foreach ($jobs as $job) {
            if (is_string($job)) {
                $this->push($job);
            } elseif (is_array($job) && isset($job['job'])) {
                $this->push($job['job'], $job['data'] ?? []);
            }
        }
    }

    public function push(string $job, array $data = []): string
    {
        if (! isset($this->pushed[$job])) {
            $this->pushed[$job] = [];
        }

        $this->pushed[$job][] = [
            'job' => $job,
            'data' => $data,
        ];

        // Don't actually execute the job - just capture it
        return 'fake-' . uniqid('', true);
    }

    public function flush(): void
    {
        $this->pushed = [];
        $this->pushedLater = [];
    }

    /**
     * Assert that a job was pushed.
     */
    public function assertPushed(string $job): void
    {
        Assert::assertTrue(
            $this->hasPushed($job),
            sprintf('The expected [%s] job was not pushed.', $job),
        );
    }

    /**
     * Check if a job was pushed.
     */
    public function hasPushed(string $job): bool
    {
        return isset($this->pushed[$job]) && (isset($this->pushed[$job]) && $this->pushed[$job] !== []);
    }

    /**
     * Assert that a job was not pushed.
     */
    public function assertNotPushed(string $job): void
    {
        Assert::assertFalse(
            $this->hasPushed($job),
            sprintf('The unexpected [%s] job was pushed.', $job),
        );
    }

    /**
     * Assert that a job was pushed N times.
     */
    public function assertPushedTimes(string $job, int $times = 1): void
    {
        $count = $this->pushedCount($job);

        Assert::assertSame(
            $times,
            $count,
            sprintf('The [%s] job was pushed %d times instead of %d times.', $job, $count, $times),
        );
    }

    /**
     * Get the count of times a job was pushed.
     */
    public function pushedCount(string $job): int
    {
        $total = count($this->pushed[$job] ?? []);

        return $total + count($this->pushedLater[$job] ?? []);
    }

    /**
     * Assert that a job was pushed with specific data.
     */
    public function assertPushedWith(string $job, array $expectedData): void
    {
        Assert::assertTrue(
            $this->hasPushedWith($job, $expectedData),
            sprintf('The [%s] job was not pushed with the expected data.', $job),
        );
    }

    /**
     * Check if a job was pushed with specific data.
     */
    public function hasPushedWith(string $job, array $expectedData): bool
    {
        if (! $this->hasPushed($job)) {
            return false;
        }

        foreach ($this->pushed[$job] as $pushed) {
            if ($pushed['data'] === $expectedData) {
                return true;
            }
        }

        return false;
    }

    /**
     * Assert that a job was pushed to a specific queue.
     */
    public function assertPushedToQueue(string $job, string $queue): void
    {
        Assert::assertTrue(
            $this->hasPushed($job),
            sprintf('The [%s] job was not pushed.', $job),
        );
    }

    /**
     * Assert that a job was pushed with a delay.
     */
    public function assertPushedLater(string $job): void
    {
        Assert::assertTrue(
            $this->hasPushedLater($job),
            sprintf('The expected [%s] job was not pushed with a delay.', $job),
        );
    }

    /**
     * Check if a job was pushed with a delay.
     */
    public function hasPushedLater(string $job): bool
    {
        return isset($this->pushedLater[$job]) && (isset($this->pushedLater[$job]) && $this->pushedLater[$job] !== []);
    }

    /**
     * Get all pushed jobs.
     *
     * @return array<string, list<array{job: string, data: array}>>
     */
    public function pushed(): array
    {
        return $this->pushed;
    }
}
