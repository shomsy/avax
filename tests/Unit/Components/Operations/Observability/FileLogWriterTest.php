<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Operations\Observability\System\Capabilities\Logging\FileLogWriter;
use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class FileLogWriterTest extends TestCase
{
    private string $tmpDir;
    private Filesystem $filesystem;

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_log_test_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->filesystem = new Filesystem();
    }

    protected function tearDown() : void
    {
        if (is_dir($this->tmpDir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );
            /** @var SplFileInfo $item */
            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    rmdir($item->getPathname());
                } else {
                    unlink($item->getPathname());
                }
            }
            rmdir($this->tmpDir);
        }
    }

    #[Test]
    public function writes_log_record_as_ndjson() : void
    {
        $path = "{$this->tmpDir}/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);

        $record = new StructuredLogRecord('info', 'Hello world', ['user_id' => 42]);
        $writer->write($record);
        $writer->close();

        self::assertFileExists($path);
        $content = file_get_contents($path);
        self::assertNotFalse($content);

        $line = trim($content);
        $data = json_decode($line, true);

        self::assertSame('info', $data['level']);
        self::assertSame('Hello world', $data['message']);
        self::assertSame(42, $data['context']['user_id']);
    }

    #[Test]
    public function redacts_sensitive_data() : void
    {
        $path = "{$this->tmpDir}/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);

        $record = new StructuredLogRecord('info', 'Login', ['password' => 'secret123']);
        $writer->write($record);
        $writer->close();

        $content = file_get_contents($path);
        self::assertNotFalse($content);

        $data = json_decode(trim($content), true);

        self::assertSame('***', $data['context']['password']);
    }

    #[Test]
    public function write_batch_writes_multiple_records() : void
    {
        $path = "{$this->tmpDir}/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);

        $records = [
            new StructuredLogRecord('info', 'First'),
            new StructuredLogRecord('warning', 'Second'),
            new StructuredLogRecord('error', 'Third'),
        ];

        $count = $writer->writeBatch($records);
        $writer->close();

        self::assertSame(3, $count);

        $lines = array_filter(explode(PHP_EOL, trim(file_get_contents($path))));
        self::assertCount(3, $lines);
    }

    #[Test]
    public function creates_directory_if_missing() : void
    {
        $path = "{$this->tmpDir}/subdir/nested/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);

        $writer->write(new StructuredLogRecord('info', 'test'));
        $writer->close();

        self::assertFileExists($path);
    }

    #[Test]
    public function throws_when_closed() : void
    {
        $path = "{$this->tmpDir}/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);
        $writer->close();

        self::expectException(RuntimeException::class);

        $writer->write(new StructuredLogRecord('info', 'should fail'));
    }

    #[Test]
    public function isOpen_reflects_state() : void
    {
        $path = "{$this->tmpDir}/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);

        self::assertFalse($writer->isOpen());

        $writer->write(new StructuredLogRecord('info', 'test'));

        self::assertTrue($writer->isOpen());

        $writer->close();

        self::assertFalse($writer->isOpen());
    }

    #[Test]
    public function path_returns_configured_path() : void
    {
        $path = "{$this->tmpDir}/test.log";
        $writer = new FileLogWriter($path, $this->filesystem);

        self::assertSame($path, $writer->path());
    }
}
