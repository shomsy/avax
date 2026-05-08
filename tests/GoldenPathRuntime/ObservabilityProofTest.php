<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves: Logger records structured logs with correlation IDs.
 */
final class ObservabilityProofTest extends TestCase
{
    #[Test]
    public function loggerRecordsStructuredLogsWithCorrelationIds() : void
    {
        $logger = new Logger();

        $record = $logger->info('Webhook received', [
            'webhook_id' => 'wh_001',
            'source'     => 'github',
        ])->withRequestId('corr_abc123');

        self::assertSame('info', $record->level);
        self::assertSame('Webhook received', $record->message);
        self::assertSame('corr_abc123', $record->requestId);
        self::assertSame('wh_001', $record->context['webhook_id']);
    }

    #[Test]
    public function logRecordToArrayIncludesRequestId() : void
    {
        $logger = new Logger();

        $record = $logger->info('test', [])->withRequestId('corr_xyz');
        $array  = $record->toArray();

        self::assertArrayHasKey('request_id', $array);
        self::assertSame('corr_xyz', $array['request_id']);
    }

    #[Test]
    public function loggerHandlesMultipleLevels() : void
    {
        $logger = new Logger();

        $logger->info('info message');
        $logger->warning('warning message');
        $logger->error('error message');
        $logger->debug('debug message');

        self::assertCount(4, $logger->records());
    }

    #[Test]
    public function loggerRecordsAreClearable() : void
    {
        $logger = new Logger();

        $logger->info('record 1');
        $logger->info('record 2');

        self::assertCount(2, $logger->records());

        $logger->clear();

        self::assertCount(0, $logger->records());
    }

    #[Test]
    public function logRecordWithContextMerges() : void
    {
        $logger = new Logger();

        $record = $logger->info('initial', ['key1' => 'value1']);
        $merged = $record->withContext(['key2' => 'value2']);

        self::assertSame('value1', $merged->context['key1']);
        self::assertSame('value2', $merged->context['key2']);
    }

    #[Test]
    public function logRecordToArrayHasTimestamp() : void
    {
        $logger = new Logger();

        $record = $logger->info('timestamped');
        $array  = $record->toArray();

        self::assertNotNull($array['timestamp']);
        self::assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T/', $array['timestamp']);
    }
}
