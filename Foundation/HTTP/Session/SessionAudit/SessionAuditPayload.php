<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionAudit;

final class SessionAuditPayload
{
    public function __construct(
        public readonly string  $event,
        public readonly array   $data,
        public readonly int     $timestamp,
        public readonly ?string $sessionId,
        public readonly ?string $actorId
    ) {}

    public static function create(
        string     $event,
        array|null $data = null,
        ?string    $sessionId = null,
        ?string    $actorId = null
    ) : self
    {
        $data ??= [];

        return new self(
            event    : $event,
            data     : $data,
            timestamp: time(),
            sessionId: $sessionId,
            actorId  : $actorId
        );
    }
}