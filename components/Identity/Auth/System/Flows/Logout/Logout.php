<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Logout;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;

/**
 * Logout - Flow orchestrator for user logout.
 * 1:1 alignment with refactor.md.
 */
final readonly class Logout
{
    public function __construct(
        private ClearAuthenticatedIdentity $clearAuthenticatedIdentity,
        private IdentityInterface          $identity,
    ) {}

    public function execute() : void
    {
        $this->clearAuthenticatedIdentity->execute();
        $this->identity->clear();
    }
}
