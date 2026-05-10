<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Logging;

use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use Avax\Components\Operations\Observability\System\Capabilities\Redaction\RedactSensitiveData;
use RuntimeException;

/**
 * Writes structured log records to a file as newline-delimited JSON.
 *
 * Each log record is written as a single JSON line (NDJSON format).
 * Suitable for development, testing, and environments where a log
 * aggregator reads from a file (e.g. fluentd, filebeat).
 */
final class FileLogWriter
{
    /** @var resource|null */
    private $handle;

    private bool $closed = false;

    public function __construct(
        private readonly string $path,
        private readonly RedactSensitiveData $redactor = new RedactSensitiveData(),
    ) {}

    public function write(StructuredLogRecord $record) : void
    {
        if ($this->closed) {
            throw new RuntimeException('Log writer is closed.');
        }

        if ($this->handle === null) {
            $this->open();
        }

        $data = $record->toArray();
        $data = $this->redactor->redact($data);

        $line = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Handle is guaranteed non-null here: closed check at top + open() above ensures it.
        \assert($this->handle !== null);
        $written = @fwrite($this->handle, $line . PHP_EOL);

        if ($written === false) {
            throw new RuntimeException("Failed to write to log file: {$this->path}");
        }
    }

    /**
     * Write multiple records in a batch.
     *
     * @param list<StructuredLogRecord> $records
     * @return int number of records successfully written
     */
    public function writeBatch(array $records) : int
    {
        $count = 0;

        foreach ($records as $record) {
            try {
                $this->write($record);
                $count++;
            } catch (RuntimeException) {
                // Best-effort: skip failed records but continue.
            }
        }

        return $count;
    }

    public function close() : void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }

        $this->closed = true;
    }

    public function isOpen() : bool
    {
        return $this->handle !== null && ! $this->closed;
    }

    public function path() : string
    {
        return $this->path;
    }

    private function open() : void
    {
        $dir = dirname($this->path);

        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        $handle = fopen($this->path, 'a');

        if ($handle === false) {
            throw new RuntimeException("Cannot open log file: {$this->path}");
        }

        $this->handle = $handle;
    }

    public function __destruct()
    {
        $this->close();
    }
}
