<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ReadCurrentUser;

use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Retrieve the currently authenticated user entity.
 *
 * Banal: Just gets the person who is logged in.
 */
final readonly class ReadCurrentUser
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication
    ) {}

    public function execute() : AuthenticatedUser|null
    {
        return $this->currentAuthentication->read()->user();
    }
}
