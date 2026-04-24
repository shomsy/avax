<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionAudit;

interface SessionAuditSink
{
    public function write(SessionAuditPayload $payload) : void;
}