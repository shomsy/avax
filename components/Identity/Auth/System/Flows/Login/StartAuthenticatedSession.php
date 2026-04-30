<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\IssuedAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;

/**
 * StartAuthenticatedSession - Action to persist authentication state in session.
 * 1:1 alignment with refactor.md.
 */
final readonly class StartAuthenticatedSession
{
    public function __construct(
        private Sessions $sessions,
    ) {}

    public function execute(IssuedAuthentication $issued) : void
    {
        $this->sessions->start($issued);
    }
}
