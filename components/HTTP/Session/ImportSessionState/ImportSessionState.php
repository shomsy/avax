<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ImportSessionState;

use Avax\Components\HTTP\Session\SessionRecovery\SessionRecovery;

final class ImportSessionState
{
    private SessionRecovery $recovery;

    public function __construct(SessionRecovery $recovery) { $this->recovery = $recovery; }

    public function handle(string $payload) : bool { return $this->recovery->import(payload: $payload); }
}
