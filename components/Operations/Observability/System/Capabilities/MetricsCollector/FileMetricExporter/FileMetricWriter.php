<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\FileMetricExporter;

use Avax\Components\Operations\Observability\System\Capabilities\Redaction\RedactSensitiveData;

/**
 * NDJSON file metric writer with redaction.
 */
final readonly class FileMetricWriter
{
    public function __construct(
        private string $filePath,
        private RedactSensitiveData $redactor = new RedactSensitiveData(),
    ) {}

    /**
     * @param array{name: string, type: string, value: float, tags: array<string, string>, timestamp: float} $metric
     */
    public function write(array $metric): void
    {
        $redacted = $this->redactor->redact($metric);
        $line = json_encode($redacted, JSON_THROW_ON_ERROR);
        file_put_contents($this->filePath, $line.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param list<array{name: string, type: string, value: float, tags: array<string, string>, timestamp: float}> $metrics
     */
    public function writeBatch(array $metrics): void
    {
        $lines = [];
        foreach ($metrics as $metric) {
            $redacted = $this->redactor->redact($metric);
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
