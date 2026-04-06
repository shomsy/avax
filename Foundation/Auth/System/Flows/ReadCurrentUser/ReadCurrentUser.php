<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ReadCurrentUser;

use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;

/**
 * Retrieve the currently authenticated user entity.
 *
 * Banal: Just gets the person who is logged in.
 */
final readonly class ReadCurrentUser
{
    public function __construct(
        #[\SensitiveParameter] private IdentityInterface $identity,
        private UserSourceInterface                       $userSource
    ) {}

    public function execute() : User|null
    {
        $currentUser = $this->identity->getCurrentUser();

        if ($currentUser !== null) {
            return $currentUser;
        }

        $userId = $this->identity->getUserId();

        if ($userId !== null) {
            return $this->userSource->findById(id: new UserId($userId));
        }

        return null;
    }
}
