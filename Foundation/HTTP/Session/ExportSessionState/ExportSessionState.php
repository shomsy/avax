<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\ExportSessionState;

use Avax\HTTP\Session\SessionRecovery\SessionRecovery;

final class ExportSessionState
{
    private SessionRecovery $recovery;
    public function __construct(SessionRecovery $recovery) { $this->recovery = $recovery; }
    public function handle() : string { return $this->recovery->export(); }
}
