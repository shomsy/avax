<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\FileMetricExporter;

use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;

/**
 * NDJSON file metric writer with redaction.
 */
final class FileMetricWriter
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'secret', 'token', 'api_key', 'authorization',
        ];

    public function __construct(
        private string $filePath,
    ) {}

    /**
     * @param array{name: string, type: string, value: float, tags: array<string, string>, timestamp: float} $metric
     */
    public function write(array $metric): void
    {
        $redacted = Redaction::redactLog(
            logData      : $metric,
            sensitiveKeys: self::$defaultSensitiveKeys,
        );
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
            $redacted = Redaction::redactLog(
                logData      : $metric,
                sensitiveKeys: self::$defaultSensitiveKeys,
            );
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
