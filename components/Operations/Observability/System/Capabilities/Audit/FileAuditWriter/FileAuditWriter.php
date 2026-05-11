<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Audit\FileAuditWriter;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;

/**
 * NDJSON file audit writer with redaction.
 * Uses Application/Filesystem for all file I/O.
 */
final class FileAuditWriter
{
    /** @var list<string> */
    private static array $defaultSensitiveKeys
        = [
            'password', 'secret', 'token', 'api_key', 'authorization',
            'access_token', 'refresh_token', 'private_key', 'secret_key',
        ];

    private Filesystem $filesystem;

    public function __construct(
        private string $filePath, Filesystem|null $filesystem = null,
    )
    {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

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
        $this->filesystem->append($this->filePath, $line . PHP_EOL);
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
