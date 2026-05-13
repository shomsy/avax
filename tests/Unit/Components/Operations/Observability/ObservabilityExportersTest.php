<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Operations\Observability\System\Capabilities\Audit\FileAuditWriter\FileAuditWriter;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\FileMetricExporter\FileMetricWriter;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\InMemoryMetricExporter\InMemoryMetricExporter;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\FileTraceExporter\FileTraceWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryMetricExporter::class)]
#[CoversClass(FileMetricWriter::class)]
#[CoversClass(FileTraceWriter::class)]
#[CoversClass(FileAuditWriter::class)]
final class ObservabilityExportersTest extends TestCase
{
    private string $tempFile;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->tempFile = sys_get_temp_dir().'/avax_observability_test_'.uniqid().'.ndjson';
        $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function test_in_memory_metric_exporter_records_and_flushes(): void
    {
        $exporter = new InMemoryMetricExporter();
        $exporter->record('http.requests', 'counter', 1.0, ['method' => 'GET']);
        $exporter->record('http.latency', 'histogram', 45.2, ['path' => '/api']);

        self::assertSame(2, $exporter->count());

        $flushed = $exporter->flush();
        self::assertCount(2, $flushed);
        self::assertSame(0, $exporter->count());
    }

    public function test_file_metric_writer_writes_and_reads(): void
    {
        $writer = new FileMetricWriter($this->tempFile, $this->filesystem);
        $metric = [
            'name' => 'http.requests',
            'type' => 'counter',
            'value' => 1.0,
            'tags' => ['method' => 'GET'],
            'timestamp' => microtime(true),
        ];

        $writer->write($metric);
        $read = $writer->read();

        self::assertCount(1, $read);
        self::assertSame('http.requests', $read[0]['name']);
    }

    public function test_file_metric_writer_batch(): void
    {
        $writer = new FileMetricWriter($this->tempFile, $this->filesystem);
        $metrics = [
            ['name' => 'metric1', 'type' => 'counter', 'value' => 1.0, 'tags' => [], 'timestamp' => 1.0],
            ['name' => 'metric2', 'type' => 'gauge', 'value' => 2.0, 'tags' => [], 'timestamp' => 2.0],
        ];

        $writer->writeBatch($metrics);
        $read = $writer->read();

        self::assertCount(2, $read);
    }

    public function test_file_trace_writer_writes_and_reads(): void
    {
        $writer = new FileTraceWriter($this->tempFile, $this->filesystem);
        $span = [
            'trace_id' => 'abc123',
            'span_id' => 'def456',
            'parent_id' => null,
            'name' => 'http.request',
            'start' => microtime(true),
            'end' => microtime(true) + 0.01,
            'status' => 'ok',
            'attributes' => ['http.method' => 'GET'],
        ];

        $writer->write($span);
        $read = $writer->read();

        self::assertCount(1, $read);
        self::assertSame('http.request', $read[0]['name']);
        self::assertSame('abc123', $read[0]['trace_id']);
    }

    public function test_file_audit_writer_writes_and_reads(): void
    {
        $writer = new FileAuditWriter($this->tempFile, $this->filesystem);
        $audit = [
            'event' => 'user.login',
            'actor' => 'admin',
            'action' => 'authenticate',
            'target' => 'user:1',
            'timestamp' => '2026-05-10T12:00:00Z',
            'details' => ['ip' => '127.0.0.1'],
        ];

        $writer->write($audit);
        $read = $writer->read();

        self::assertCount(1, $read);
        self::assertSame('user.login', $read[0]['event']);
    }

    public function test_redaction_applies_before_write(): void
    {
        $writer = new FileMetricWriter($this->tempFile, $this->filesystem);
        $metric = [
            'name' => 'db.query',
            'type' => 'histogram',
            'value' => 12.5,
            'tags' => ['password' => 'secret123', 'user' => 'admin'],
            'timestamp' => microtime(true),
        ];

        $writer->write($metric);
        $content = file_get_contents($this->tempFile);
        self::assertNotFalse($content);
        self::assertStringNotContainsString('secret123', $content);
    }

    public function test_file_writer_clears(): void
    {
        $writer = new FileMetricWriter($this->tempFile, $this->filesystem);
        $writer->write(['name' => 'test', 'type' => 'counter', 'value' => 1.0, 'tags' => [], 'timestamp' => 1.0]);
        self::assertCount(1, $writer->read());

        $writer->clear();
        self::assertCount(0, $writer->read());
    }

    public function test_file_writer_returns_empty_for_missing_file(): void
    {
        $writer = new FileMetricWriter('/nonexistent/path/file.ndjson', $this->filesystem);
        self::assertSame([], $writer->read());
    }
}
