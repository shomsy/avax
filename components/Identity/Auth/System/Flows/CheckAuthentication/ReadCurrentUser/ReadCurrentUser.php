<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\ReadCurrentUser;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

/**
 * Retrieve the currently authenticated user entity.
 *
 * Banal: Just gets the person who is logged in.
 */
final readonly class ReadCurrentUser
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
    ) {
    }

    public function execute(): ?AuthenticatedUser
    {
        return $this->currentAuthentication->read()->user();
    }
}
