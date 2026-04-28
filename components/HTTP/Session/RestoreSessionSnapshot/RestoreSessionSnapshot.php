<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\RestoreSessionSnapshot;

use Avax\Components\HTTP\Session\SessionRecovery\SessionRecovery;
use Exception;

final class RestoreSessionSnapshot
{
    private SessionRecovery $recovery;

    public function __construct(SessionRecovery $recovery) { $this->recovery = $recovery; }

    /**
     * @throws Exception
     */
    public function handle(string $name = 'default') : void { $this->recovery->restore(name: $name); }
}
