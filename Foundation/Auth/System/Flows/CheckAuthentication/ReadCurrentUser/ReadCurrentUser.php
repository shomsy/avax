<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ReadCurrentUser;

use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Retrieve the currently authenticated user entity.
 *
 * Banal: Just gets the person who is logged in.
 */
final readonly class ReadCurrentUser
{
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication
    )
    {
        $this->currentAuthentication = $currentAuthentication;
    }

    public function execute() : AuthenticatedUser|null
    {
        return $this->currentAuthentication->read()->user();
    }
}
