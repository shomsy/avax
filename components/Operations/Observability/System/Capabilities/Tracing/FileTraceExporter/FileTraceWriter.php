<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing\FileTraceExporter;

use Avax\Components\Operations\Observability\System\Capabilities\Redaction\RedactSensitiveData;

/**
 * NDJSON file trace/span writer with redaction.
 */
final readonly class FileTraceWriter
{
    public function __construct(
        private string $filePath,
        private RedactSensitiveData $redactor = new RedactSensitiveData(),
    ) {}

    /**
     * @param array{trace_id: string, span_id: string, parent_id: string|null, name: string, start: float, end: float|null, status: string, attributes: array<string, mixed>} $span
     */
    public function write(array $span): void
    {
        $redacted = $this->redactor->redact($span);
        $line = json_encode($redacted, JSON_THROW_ON_ERROR);
        file_put_contents($this->filePath, $line.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param list<array{trace_id: string, span_id: string, parent_id: string|null, name: string, start: float, end: float|null, status: string, attributes: array<string, mixed>}> $spans
     */
    public function writeBatch(array $spans): void
    {
        $lines = [];
        foreach ($spans as $span) {
            $redacted = $this->redactor->redact($span);
            $lines[] = json_encode($redacted, JSON_THROW_ON_ERROR);
        }
        file_put_contents($this->filePath, implode(PHP_EOL, $lines).PHP_EOL, LOCK_EX);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);
        if ($content === '' || $content === false) {
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
        if (file_exists($this->filePath)) {
            file_put_contents($this->filePath, '', LOCK_EX);
        }
    }
}
