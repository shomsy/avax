<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Logout;

use Avax\Auth\System\Capabilities\Identity\IdentityInterface;

/**
 * High-level orchestrator for the logout process.
 *
 * Banal: The Logout file.
 */
final readonly class Logout
{
    public function __construct(
        #[\SensitiveParameter] private IdentityInterface $identity
    ) {}

    public function execute() : void
    {
        $this->identity->clear();
    }
}
