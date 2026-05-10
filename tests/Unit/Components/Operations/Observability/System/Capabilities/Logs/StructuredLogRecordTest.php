<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Logs;

use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StructuredLogRecordTest extends TestCase
{
    #[Test]
    public function it_constructs_with_required_fields() : void
    {
        $record = new StructuredLogRecord('info', 'User logged in');

        self::assertSame('info', $record->level);
        self::assertSame('User logged in', $record->message);
        self::assertSame([], $record->context);
        self::assertNull($record->timestamp);
        self::assertNull($record->requestId);
    }

    #[Test]
    public function it_constructs_with_context() : void
    {
        $record = new StructuredLogRecord('error', 'Database connection failed', [
            'host' => 'localhost',
            'port' => 5432,
        ]);

        self::assertSame(['host' => 'localhost', 'port' => 5432], $record->context);
    }

    #[Test]
    public function it_constructs_with_timestamp() : void
    {
        $record = new StructuredLogRecord('debug', 'test', [], '2024-01-01T00:00:00Z');

        self::assertSame('2024-01-01T00:00:00Z', $record->timestamp);
    }

    #[Test]
    public function it_constructs_with_request_id() : void
    {
        $record = new StructuredLogRecord('info', 'test', [], null, 'req-123');

        self::assertSame('req-123', $record->requestId);
    }

    #[Test]
    public function it_constructs_with_all_fields() : void
    {
        $record = new StructuredLogRecord(
            'warning',
            'Slow query detected',
            ['query_time' => 5.2],
            '2024-06-15T12:00:00Z',
            'req-456',
        );

        self::assertSame('warning', $record->level);
        self::assertSame('Slow query detected', $record->message);
        self::assertSame(['query_time' => 5.2], $record->context);
        self::assertSame('2024-06-15T12:00:00Z', $record->timestamp);
        self::assertSame('req-456', $record->requestId);
    }

    #[Test]
    public function it_merges_context_with_with_context() : void
    {
        $record = new StructuredLogRecord('info', 'test', ['a' => 1]);

        $newRecord = $record->withContext(['b' => 2]);

        self::assertSame(['a' => 1, 'b' => 2], $newRecord->context);
    }

    #[Test]
    public function it_overwrites_existing_keys_in_with_context() : void
    {
        $record = new StructuredLogRecord('info', 'test', ['key' => 'original']);

        $newRecord = $record->withContext(['key' => 'updated']);

        self::assertSame(['key' => 'updated'], $newRecord->context);
    }

    #[Test]
    public function it_preserves_original_when_merging_context() : void
    {
        $record = new StructuredLogRecord('info', 'test', ['a' => 1]);

        $newRecord = $record->withContext(['b' => 2]);

        self::assertSame(['a' => 1], $record->context);
        self::assertSame(['a' => 1, 'b' => 2], $newRecord->context);
    }

    #[Test]
    public function it_sets_request_id_with_with_request_id() : void
    {
        $record = new StructuredLogRecord('info', 'test');

        $newRecord = $record->withRequestId('req-789');

        self::assertSame('req-789', $newRecord->requestId);
    }

    #[Test]
    public function it_preserves_original_when_setting_request_id() : void
    {
        $record = new StructuredLogRecord('info', 'test', [], null, 'original-req');

        $newRecord = $record->withRequestId('new-req');

        self::assertSame('original-req', $record->requestId);
        self::assertSame('new-req', $newRecord->requestId);
    }

    #[Test]
    public function it_returns_new_instance_on_with_context() : void
    {
        $record = new StructuredLogRecord('info', 'test');

        $newRecord = $record->withContext(['key' => 'value']);

        self::assertNotSame($record, $newRecord);
    }

    #[Test]
    public function it_returns_new_instance_on_with_request_id() : void
    {
        $record = new StructuredLogRecord('info', 'test');

        $newRecord = $record->withRequestId('req-1');

        self::assertNotSame($record, $newRecord);
    }

    #[Test]
    public function it_converts_to_array() : void
    {
        $record = new StructuredLogRecord('error', 'Connection failed', ['host' => 'db']);

        $array = $record->toArray();

        self::assertSame('error', $array['level']);
        self::assertSame('Connection failed', $array['message']);
        self::assertSame(['host' => 'db'], $array['context']);
        self::assertNull($array['request_id']);
    }

    #[Test]
    public function it_generates_timestamp_in_to_array_when_null() : void
    {
        $record = new StructuredLogRecord('info', 'test');

        $array = $record->toArray();

        self::assertIsString($array['timestamp']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $array['timestamp']);
    }

    #[Test]
    public function it_preserves_explicit_timestamp_in_to_array() : void
    {
        $record = new StructuredLogRecord('info', 'test', [], '2024-01-01T00:00:00Z');

        $array = $record->toArray();

        self::assertSame('2024-01-01T00:00:00Z', $array['timestamp']);
    }

    #[Test]
    public function it_includes_request_id_in_array() : void
    {
        $record = new StructuredLogRecord('info', 'test', [], null, 'req-abc');

        $array = $record->toArray();

        self::assertSame('req-abc', $array['request_id']);
    }

    #[Test]
    public function it_handles_empty_context_in_to_array() : void
    {
        $record = new StructuredLogRecord('info', 'test');

        $array = $record->toArray();

        self::assertSame([], $array['context']);
    }

    #[Test]
    public function it_handles_various_log_levels() : void
    {
        $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

        foreach ($levels as $level) {
            $record = new StructuredLogRecord($level, "test $level");
            self::assertSame($level, $record->level);
        }
    }

    #[Test]
    public function it_handles_complex_context() : void
    {
        $context = [
            'user'    => ['id' => 1, 'name' => 'Alice'],
            'request' => ['method' => 'POST', 'path' => '/api/users'],
            'tags'    => ['auth', 'api'],
        ];

        $record = new StructuredLogRecord('info', 'User action', $context);

        self::assertSame($context, $record->context);
    }
}
