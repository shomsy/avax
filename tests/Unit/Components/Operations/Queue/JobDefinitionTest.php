<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JobDefinitionTest extends TestCase
{
    #[Test]
    public function it_constructs_with_defaults() : void
    {
        $job = new JobDefinition(handler: 'SendEmail');

        self::assertSame('SendEmail', $job->handler);
        self::assertSame([], $job->payload);
        self::assertNull($job->queue);
        self::assertSame(3, $job->maxAttempts);
        self::assertSame(60, $job->timeout);
        self::assertSame(0, $job->retryDelay);
        self::assertNull($job->correlationId);
    }

    #[Test]
    public function it_constructs_with_custom_values() : void
    {
        $job = new JobDefinition(
            handler      : 'ProcessPayment',
            payload      : ['amount' => 100, 'currency' => 'USD'],
            queue        : 'payments',
            maxAttempts  : 5,
            timeout      : 30,
            retryDelay   : 10,
            correlationId: 'corr-123',
        );

        self::assertSame('ProcessPayment', $job->handler);
        self::assertSame(['amount' => 100, 'currency' => 'USD'], $job->payload);
        self::assertSame('payments', $job->queue);
        self::assertSame(5, $job->maxAttempts);
        self::assertSame(30, $job->timeout);
        self::assertSame(10, $job->retryDelay);
        self::assertSame('corr-123', $job->correlationId);
    }

    #[Test]
    public function it_creates_from_array_with_all_fields() : void
    {
        $data = [
            'handler'       => 'GenerateReport',
            'payload'       => ['reportId' => 42],
            'queue'         => 'reports',
            'maxAttempts'   => 2,
            'timeout'       => 120,
            'retryDelay'    => 5,
            'correlationId' => 'corr-456',
        ];

        $job = JobDefinition::fromArray($data);

        self::assertSame('GenerateReport', $job->handler);
        self::assertSame(['reportId' => 42], $job->payload);
        self::assertSame('reports', $job->queue);
        self::assertSame(2, $job->maxAttempts);
        self::assertSame(120, $job->timeout);
        self::assertSame(5, $job->retryDelay);
        self::assertSame('corr-456', $job->correlationId);
    }

    #[Test]
    public function it_creates_from_array_with_minimal_fields() : void
    {
        $data = ['handler' => 'SimpleTask'];

        $job = JobDefinition::fromArray($data);

        self::assertSame('SimpleTask', $job->handler);
        self::assertSame([], $job->payload);
        self::assertNull($job->queue);
        self::assertSame(3, $job->maxAttempts);
        self::assertSame(60, $job->timeout);
        self::assertSame(0, $job->retryDelay);
        self::assertNull($job->correlationId);
    }

    #[Test]
    public function it_creates_immutable_copy_with_new_payload() : void
    {
        $original = new JobDefinition(
            handler: 'UpdateUser',
            payload: ['id' => 1],
            queue  : 'users',
        );

        $modified = $original->withPayload(['id' => 2, 'name' => 'Alice']);

        self::assertNotSame($original, $modified);
        self::assertSame(['id' => 1], $original->payload);
        self::assertSame(['id' => 2, 'name' => 'Alice'], $modified->payload);
        self::assertSame('UpdateUser', $modified->handler);
        self::assertSame('users', $modified->queue);
    }

    #[Test]
    public function it_creates_immutable_copy_on_new_queue() : void
    {
        $original = new JobDefinition(handler: 'Notify', queue: 'default');

        $modified = $original->onQueue('high-priority');

        self::assertNotSame($original, $modified);
        self::assertSame('default', $original->queue);
        self::assertSame('high-priority', $modified->queue);
        self::assertSame('Notify', $modified->handler);
    }

    #[Test]
    public function it_creates_immutable_copy_with_new_max_attempts() : void
    {
        $original = new JobDefinition(handler: 'RetryTask', maxAttempts: 3);

        $modified = $original->withMaxAttempts(10);

        self::assertNotSame($original, $modified);
        self::assertSame(3, $original->maxAttempts);
        self::assertSame(10, $modified->maxAttempts);
    }

    #[Test]
    public function it_creates_immutable_copy_with_new_timeout() : void
    {
        $original = new JobDefinition(handler: 'LongTask', timeout: 60);

        $modified = $original->withTimeout(300);

        self::assertNotSame($original, $modified);
        self::assertSame(60, $original->timeout);
        self::assertSame(300, $modified->timeout);
    }

    #[Test]
    public function it_creates_immutable_copy_with_new_retry_delay() : void
    {
        $original = new JobDefinition(handler: 'DelayedTask', retryDelay: 0);

        $modified = $original->withRetryDelay(30);

        self::assertNotSame($original, $modified);
        self::assertSame(0, $original->retryDelay);
        self::assertSame(30, $modified->retryDelay);
    }

    #[Test]
    public function it_creates_immutable_copy_with_new_correlation_id() : void
    {
        $original = new JobDefinition(handler: 'TracedTask');

        $modified = $original->withCorrelationId('trace-abc');

        self::assertNotSame($original, $modified);
        self::assertNull($original->correlationId);
        self::assertSame('trace-abc', $modified->correlationId);
    }

    #[Test]
    public function it_converts_to_array_with_all_fields() : void
    {
        $job = new JobDefinition(
            handler      : 'ExportData',
            payload      : ['format' => 'csv'],
            queue        : 'exports',
            maxAttempts  : 1,
            timeout      : 180,
            retryDelay   : 15,
            correlationId: 'corr-789',
        );

        $array = $job->toArray();

        self::assertSame('ExportData', $array['handler']);
        self::assertSame(['format' => 'csv'], $array['payload']);
        self::assertSame('exports', $array['queue']);
        self::assertSame(1, $array['maxAttempts']);
        self::assertSame(180, $array['timeout']);
        self::assertSame(15, $array['retryDelay']);
        self::assertSame('corr-789', $array['correlationId']);
        self::assertArrayHasKey('createdAt', $array);
        self::assertIsInt($array['createdAt']);
    }

    #[Test]
    public function it_generates_correlation_id_when_converting_to_array_without_one() : void
    {
        $job = new JobDefinition(handler: 'AutoCorr');

        $array = $job->toArray();

        self::assertArrayHasKey('correlationId', $array);
        self::assertNotNull($array['correlationId']);
        self::assertStringStartsWith('corr-', $array['correlationId']);
    }

    #[Test]
    public function it_includes_id_in_array_representation() : void
    {
        $job   = new JobDefinition(handler: 'Trackable');
        $array = $job->toArray();

        $array['id'] = 'job-001';

        self::assertSame('job-001', $array['id']);
    }

    #[Test]
    public function it_is_fully_readonly() : void
    {
        $job = new JobDefinition(
            handler      : 'Immutable',
            payload      : ['key' => 'value'],
            queue        : 'test',
            maxAttempts  : 7,
            timeout      : 45,
            retryDelay   : 3,
            correlationId: 'readonly-test',
        );

        self::assertSame('Immutable', $job->handler);
        self::assertSame(['key' => 'value'], $job->payload);
        self::assertSame('test', $job->queue);
        self::assertSame(7, $job->maxAttempts);
        self::assertSame(45, $job->timeout);
        self::assertSame(3, $job->retryDelay);
        self::assertSame('readonly-test', $job->correlationId);
    }

    #[Test]
    public function it_handles_complex_payload() : void
    {
        $complexPayload = [
            'user'     => ['id' => 1, 'roles' => ['admin', 'editor']],
            'metadata' => ['source' => 'api', 'timestamp' => 1700000000],
            'nested'   => ['a' => ['b' => ['c' => 'deep']]],
        ];

        $job = new JobDefinition(handler: 'ComplexTask', payload: $complexPayload);

        self::assertSame($complexPayload, $job->payload);

        $array = $job->toArray();
        self::assertSame($complexPayload, $array['payload']);
    }

    #[Test]
    public function from_array_ignores_extra_fields() : void
    {
        $data = [
            'handler'       => 'IgnoreExtra',
            'unknown_field' => 'should_be_ignored',
            'another_extra' => 123,
        ];

        $job = JobDefinition::fromArray($data);

        self::assertSame('IgnoreExtra', $job->handler);
        self::assertObjectNotHasProperty('unknown_field', $job);
    }
}
