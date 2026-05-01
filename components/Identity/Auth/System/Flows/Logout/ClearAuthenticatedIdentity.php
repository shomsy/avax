<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Logout;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;

/**
 * ClearAuthenticatedIdentity - Action to clear authentication state from current context.
 * 1:1 alignment with refactor.md.
 */
final readonly class ClearAuthenticatedIdentity
{
    public function __construct(
        private Sessions $sessions,
    ) {}

    public function execute(): void
    {
        $this->sessions->clear();
    }
}
