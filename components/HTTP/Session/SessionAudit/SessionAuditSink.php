<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionAudit;

interface SessionAuditSink
{
    public function write(SessionAuditPayload $payload) : void;
}