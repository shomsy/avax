<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BeginSessionTransaction;

use Avax\HTTP\Session\SessionRecovery\SessionRecovery;

final class BeginSessionTransaction
{
    private SessionRecovery $recovery;

    public function __construct(SessionRecovery $recovery) { $this->recovery = $recovery; }

    public function handle(callable $cb) : mixed { return $this->recovery->transaction(operation: $cb); }
}
