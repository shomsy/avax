<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\InMemoryFailedJobsStore;
use PHPUnit\Framework\TestCase;

final class FailedJobsStoreTest extends TestCase
{
    public function testRecordAndListFailedJobs() : void
    {
        $store = new InMemoryFailedJobsStore();

        $store->record(
            queue   : 'default',
            payload : ['id' => 'job_1', 'data' => ['key' => 'value']],
            reason  : 'Connection timeout',
            failedAt: '2026-05-10 12:00:00',
        );

        $failed = $store->list('default');

        self::assertCount(1, $failed);
        self::assertSame('job_1', $failed[0]['id']);
        self::assertSame('Connection timeout', $failed[0]['reason']);
        self::assertSame('2026-05-10 12:00:00', $failed[0]['failed_at']);
    }

    public function testListAllQueuesWhenEmptyString() : void
    {
        $store = new InMemoryFailedJobsStore();

        $store->record(queue: 'emails', payload: ['id' => 'e1'], reason: 'SMTP error', failedAt: '2026-05-10 12:00:00');
        $store->record(queue: 'notifications', payload: ['id' => 'n1'], reason: 'API timeout', failedAt: '2026-05-10 12:01:00');

        $all = $store->list('');

        self::assertCount(2, $all);
    }

    public function testCountFailedJobs() : void
    {
        $store = new InMemoryFailedJobsStore();

        $store->record(queue: 'default', payload: ['id' => 'job_1'], reason: 'err', failedAt: '2026-05-10 12:00:00');
        $store->record(queue: 'default', payload: ['id' => 'job_2'], reason: 'err', failedAt: '2026-05-10 12:01:00');

        self::assertSame(2, $store->count('default'));
        self::assertSame(0, $store->count('other'));
        self::assertSame(2, $store->count(''));
    }

    public function testClearSpecificQueue() : void
    {
        $store = new InMemoryFailedJobsStore();

        $store->record(queue: 'keep', payload: ['id' => 'k1'], reason: 'err', failedAt: '2026-05-10 12:00:00');
        $store->record(queue: 'clear', payload: ['id' => 'c1'], reason: 'err', failedAt: '2026-05-10 12:01:00');

        $store->clear('clear');

        self::assertSame(1, $store->count('keep'));
        self::assertSame(0, $store->count('clear'));
    }

    public function testClearAllQueues() : void
    {
        $store = new InMemoryFailedJobsStore();

        $store->record(queue: 'a', payload: ['id' => 'a1'], reason: 'err', failedAt: '2026-05-10 12:00:00');
        $store->record(queue: 'b', payload: ['id' => 'b1'], reason: 'err', failedAt: '2026-05-10 12:01:00');

        $store->clear();

        self::assertSame(0, $store->count(''));
    }

    public function testStoreIsInstanceScoped() : void
    {
        $store1 = new InMemoryFailedJobsStore();
        $store2 = new InMemoryFailedJobsStore();

        $store1->record(queue: 'default', payload: ['id' => 'x'], reason: 'err', failedAt: '2026-05-10 12:00:00');

        self::assertSame(1, $store1->count('default'));
        self::assertSame(0, $store2->count('default'));
    }

    public function testPreservesPayloadAndMetadata() : void
    {
        $store = new InMemoryFailedJobsStore();

        $payload = [
            'id'       => 'job_42',
            'attempts' => 5,
            'class'    => 'SendEmailJob',
            'data'     => ['to' => 'user@example.com'],
        ];

        $store->record(queue: 'emails', payload: $payload, reason: 'Max attempts exceeded', failedAt: '2026-05-10 13:00:00');

        $failed = $store->list('emails');

        self::assertSame('job_42', $failed[0]['id']);
        self::assertSame(5, $failed[0]['attempts']);
        self::assertSame('SendEmailJob', $failed[0]['class']);
        self::assertSame(['to' => 'user@example.com'], $failed[0]['data']);
    }
}
