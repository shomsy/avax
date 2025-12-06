<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\VO;

use DateTimeImmutable;

/**
 * SessionEventMetadata
 *
 * Value object for event metadata.
 *
 * @package Avax\HTTP\Session\Features\Events\VO
 */
final readonly class SessionEventMetadata
{
    /**
     * SessionEventMetadata Constructor.
     *
     * @param string            $correlationId Correlation ID.
     * @param DateTimeImmutable $timestamp     Event timestamp.
     * @param string|null       $userId        User ID if available.
     * @param string|null       $sessionId     Session ID.
     */
    public function __construct(
        public string $correlationId,
        public DateTimeImmutable $timestamp,
        public string|null $userId = null,
        public string|null $sessionId = null
    ) {}

    /**
     * Create from session context.
     *
     * @param string      $correlationId Correlation ID.
     * @param string|null $sessionId     Session ID.
     * @param string|null $userId        User ID.
     *
     * @return self
     */
    public static function create(
        string $correlationId,
        string|null $sessionId = null,
        string|null $userId = null
    ): self {
        return new self(
            correlationId: $correlationId,
            timestamp: new DateTimeImmutable(),
            userId: $userId,
            sessionId: $sessionId
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'correlation_id' => $this->correlationId,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
        ];
    }
}
