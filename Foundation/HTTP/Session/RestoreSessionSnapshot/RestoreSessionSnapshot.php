<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RestoreSessionSnapshot;

use Avax\HTTP\Session\SessionRecovery\SessionRecovery;

final class RestoreSessionSnapshot
{
    private SessionRecovery $recovery;
    public function __construct(SessionRecovery $recovery) { $this->recovery = $recovery; }
    public function handle(string $name = 'default') : void { $this->recovery->restore(name: $name); }
}
