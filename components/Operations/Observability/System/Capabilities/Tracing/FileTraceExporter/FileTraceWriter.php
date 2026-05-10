<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing\FileTraceExporter;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;

/**
 * NDJSON file trace/span writer with redaction.
 * Uses Application/Filesystem for all file I/O.
 */
final class FileTraceWriter
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'secret', 'token', 'api_key', 'authorization',
        ];

    private Filesystem $filesystem;

    public function __construct(
        private string $filePath,
        ?Filesystem $filesystem = null,
    )
    {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * @param array{trace_id: string, span_id: string, parent_id: string|null, name: string, start: float, end: float|null, status: string, attributes: array<string, mixed>} $span
     */
    public function write(array $span): void
    {
        $redacted = Redaction::redactLog(
            logData      : $span,
            sensitiveKeys: self::$defaultSensitiveKeys,
        );
        $line = json_encode($redacted, JSON_THROW_ON_ERROR);
        $this->filesystem->append($this->filePath, $line . PHP_EOL);
    }

    /**
     * @param list<array{trace_id: string, span_id: string, parent_id: string|null, name: string, start: float, end: float|null, status: string, attributes: array<string, mixed>}> $spans
     */
    public function writeBatch(array $spans): void
    {
        $lines = [];
        foreach ($spans as $span) {
            $redacted = Redaction::redactLog(
                logData      : $span,
                sensitiveKeys: self::$defaultSensitiveKeys,
            );
            $lines[] = json_encode($redacted, JSON_THROW_ON_ERROR);
        }
        $this->filesystem->append($this->filePath, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function read(): array
    {
        if (! $this->filesystem->exists($this->filePath)) {
            return [];
        }

        $content = $this->filesystem->read($this->filePath);
        if ($content === '') {
            return [];
        }

        $lines = array_filter(explode(PHP_EOL, trim($content)));
        $result = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                $result[] = $decoded;
            }
        }

        return $result;
    }

    public function clear(): void
    {
        if ($this->filesystem->exists($this->filePath)) {
            $this->filesystem->write($this->filePath, '');
        }
    }
}
