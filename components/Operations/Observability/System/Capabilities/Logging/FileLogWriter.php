<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Logging;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Operations\Observability\System\Capabilities\Logs\StructuredLogRecord;
use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;
use RuntimeException;
use function assert;

/**
 * Writes structured log records to a file as newline-delimited JSON.
 *
 * Each log record is written as a single JSON line (NDJSON format).
 * Suitable for development, testing, and environments where a log
 * aggregator reads from a file (e.g. fluentd, filebeat).
 *
 * Uses Application/Filesystem for directory creation.
 * Uses native fopen/fwrite for batch write performance (file handle stays open).
 */
final class FileLogWriter
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'secret', 'token', 'api_key', 'authorization',
            'access_token', 'refresh_token', 'private_key', 'secret_key',
        ];

    private Filesystem $filesystem;

    /** @var resource|null */
    private $handle;

    private bool $closed = false;

    public function __construct(
        private readonly string $path, Filesystem $filesystem,
    )
    {
        $this->filesystem = $filesystem;
    }

    public function write(StructuredLogRecord $record) : void
    {
        if ($this->closed) {
            throw new RuntimeException('Log writer is closed.');
        }

        if ($this->handle === null) {
            $this->open();
        }

        $data = $record->toArray();
        $data = Redaction::redactLog(
            logData      : $data,
            sensitiveKeys: self::$defaultSensitiveKeys,
        );

        $line = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        assert($this->handle !== null);
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

        if (! $this->filesystem->exists($dir)) {
            $this->filesystem->createDirectory($dir, 0o755);
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
