<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSource;

/**
 * FindUserByCredentials - Action to resolve user entity by email.
 * 1:1 alignment with refactor.md.
 */
final readonly class FindUserByCredentials
{
    public function __construct(
        private UserSource $source
    ) {}

    public function execute(string $email) : ?User
    {
        return $this->source->findByEmail($email);
    }
}
