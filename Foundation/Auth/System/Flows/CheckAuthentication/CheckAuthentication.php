<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\CheckAuthentication;

use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Check if there is an active authentication state.
 *
 * Banal: Screaming flow name.
 */
final readonly class CheckAuthentication
{
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication
    )
    {
        $this->currentAuthentication = $currentAuthentication;
    }

    public function execute() : bool
    {
        return $this->currentAuthentication->read()->isAuthenticated();
    }
}
