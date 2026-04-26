<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionAudit;

use SensitiveParameter;

final class SessionAuditPayload
{
    public function __construct(
        public readonly string                            $event,
        public readonly array                             $data,
        public readonly int                               $timestamp,
        #[SensitiveParameter] public readonly string|null $sessionId,
        public readonly string|null                       $actorId
    ) {}

    public static function create(
        string                            $event,
        array|null                        $data = null,
        #[SensitiveParameter] string|null $sessionId = null,
        string|null                       $actorId = null
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