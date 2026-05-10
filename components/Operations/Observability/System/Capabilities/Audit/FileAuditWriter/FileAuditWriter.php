<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Audit\FileAuditWriter;

use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;

/**
 * NDJSON file audit writer with redaction.
 */
final class FileAuditWriter
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'secret', 'token', 'api_key', 'authorization',
            'access_token', 'refresh_token', 'private_key', 'secret_key',
        ];

    public function __construct(
        private string $filePath,
    ) {}

    /**
     * @param array{event: string, actor: string, action: string, target: string, timestamp: string, details: array<string, mixed>} $audit
     */
    public function write(array $audit): void
    {
        $redacted = Redaction::redactLog(
            logData      : $audit,
            sensitiveKeys: self::$defaultSensitiveKeys,
        );
        $line = json_encode($redacted, JSON_THROW_ON_ERROR);
        file_put_contents($this->filePath, $line.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param list<array{event: string, actor: string, action: string, target: string, timestamp: string, details: array<string, mixed>}> $audits
     */
    public function writeBatch(array $audits): void
    {
        $lines = [];
        foreach ($audits as $audit) {
            $redacted = Redaction::redactLog(
                logData      : $audit,
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
