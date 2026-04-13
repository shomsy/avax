<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;
use JsonException;
use RuntimeException;

/**
 * Exports audit events as JSON lines, optionally chained for tamper evidence.
 */
final class JsonLinesAuditExporter implements AuditExporterInterface
{
    private string|null $previousHash = null;

    public function __construct(
        private readonly string $path,
        private readonly NormalizeAuditEvent $normalizeAuditEvent = new NormalizeAuditEvent(),
        private readonly bool $tamperEvident = true
    ) {}

    public function export(array $events) : void
    {
        $lines = [];

        foreach ($events as $event) {
            $payload = $this->normalizeAuditEvent->execute($event);

            if ($this->tamperEvident) {
                $payload['previous_hash'] = $this->previousHash;
                $payload['record_hash'] = $this->hashPayload($payload);
                $this->previousHash = $payload['record_hash'];
            }

            $lines[] = $this->encode($payload);
        }

        if ($lines === []) {
            return;
        }

        file_put_contents($this->path, implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hashPayload(array $payload) : string
    {
        return hash('sha256', $this->encode($payload));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(array $payload) : string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Audit payload could not be encoded.', previous: $exception);
        }
    }
}
