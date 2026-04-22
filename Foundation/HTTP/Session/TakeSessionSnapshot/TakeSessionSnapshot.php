<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TakeSessionSnapshot;

use Avax\HTTP\Session\SessionRecovery\SessionRecovery;

final class TakeSessionSnapshot
{
    private SessionRecovery $recovery;
    public function __construct(SessionRecovery $recovery) { $this->recovery = $recovery; }
    public function handle(string $name = 'default') : void { $this->recovery->backup(name: $name); }
}
