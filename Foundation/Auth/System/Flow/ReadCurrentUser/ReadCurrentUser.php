<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ReadCurrentUser;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use SensitiveParameter;

/**
 * Retrieve the currently authenticated user entity.
 *
 * Banal: Just gets the person who is logged in.
 */
final readonly class ReadCurrentUser
{
    public function __construct(
        #[SensitiveParameter] private IdentityInterface $identity,
        private UserSourceInterface                     $userSource
    ) {}

    public function execute() : User|null
    {
        $currentUser = $this->identity->getCurrentUser();

        if ($currentUser !== null) {
            return $currentUser->isActive() ? $currentUser : null;
        }

        $userId = $this->identity->getUserId();

        if ($userId !== null) {
            $user = $this->userSource->findById(id: new UserId(value: $userId));

            if ($user !== null && $user->isActive()) {
                return $user;
            }
        }

        return null;
    }
}
