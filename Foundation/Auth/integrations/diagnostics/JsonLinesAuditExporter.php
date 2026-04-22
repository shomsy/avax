<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditExporterInterface;
use JsonException;
use RuntimeException;

/**
 * Exports audit events as JSON lines, optionally chained for tamper evidence.
 */
final class JsonLinesAuditExporter implements AuditExporterInterface
{
    private string|null                  $previousHash = null;
    private readonly NormalizeAuditEvent $normalizeAuditEvent;

    public function __construct(
        private readonly string $path,
        NormalizeAuditEvent|null $normalizeAuditEvent = null,
        private readonly bool   $tamperEvident = true
    )
    {
        $normalizeAuditEvent       ??= new NormalizeAuditEvent();
        $this->normalizeAuditEvent = $normalizeAuditEvent;
    }

    public function export(array $events) : void
    {
        $lines = [];

        foreach ($events as $event) {
            $payload = $this->normalizeAuditEvent->execute(event: $event);

            if ($this->tamperEvident) {
                $payload['previous_hash'] = $this->previousHash;
                $payload['record_hash']   = $this->hashPayload(payload: $payload);
                $this->previousHash       = $payload['record_hash'];
            }

            $lines[] = $this->encode(payload: $payload);
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
        return hash('sha256', $this->encode(payload: $payload));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(array $payload) : string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Audit payload could not be encoded.', previous: $exception);
        }
    }
}
