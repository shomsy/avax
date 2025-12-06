<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Snapshots;

/**
 * SessionState
 *
 * Value object representing session state snapshot.
 *
 * @package Avax\HTTP\Session\Features\Snapshots
 */
final readonly class SessionState
{
    /**
     * SessionState Constructor.
     *
     * @param string               $sessionId Session ID.
     * @param array<string, mixed> $data      Session data.
     * @param array<string, mixed> $meta      Metadata.
     * @param int                  $timestamp Snapshot timestamp.
     */
    public function __construct(
        public string $sessionId,
        public array $data,
        public array $meta,
        public int $timestamp
    ) {}

    /**
     * Create from current session.
     *
     * @param string               $sessionId Session ID.
     * @param array<string, mixed> $data      Session data.
     * @param array<string, mixed> $meta      Metadata.
     *
     * @return self
     */
    public static function capture(string $sessionId, array $data, array $meta = []): self
    {
        return new self($sessionId, $data, $meta, time());
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'data' => $this->data,
            'meta' => $this->meta,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Create from array.
     *
     * @param array<string, mixed> $data The array data.
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['session_id'],
            data: $data['data'],
            meta: $data['meta'] ?? [],
            timestamp: $data['timestamp']
        );
    }
}
