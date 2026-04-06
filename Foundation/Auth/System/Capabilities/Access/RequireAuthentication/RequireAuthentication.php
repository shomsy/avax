<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireAuthentication;

use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;

/**
 * Requirement for authentication presence.
 * 
 * Banal: Stops anyone not logged in.
 */
final readonly class RequireAuthentication
{
    public function __construct(
        #[\SensitiveParameter] private CheckAuthentication $checkAuthentication
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute() : void
    {
        if (! $this->checkAuthentication->execute()) {
            throw new Unauthenticated();
        }
    }
}
