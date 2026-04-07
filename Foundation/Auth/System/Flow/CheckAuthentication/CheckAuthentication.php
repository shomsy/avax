<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\CheckAuthentication;

use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use SensitiveParameter;

/**
 * Check if there is an active authentication state.
 *
 * Banal: Screaming flow name.
 */
final readonly class CheckAuthentication
{
    public function __construct(
        #[SensitiveParameter] private ReadCurrentUser $readCurrentUser
    ) {}

    public function execute() : bool
    {
        return $this->readCurrentUser->execute() !== null;
    }
}
