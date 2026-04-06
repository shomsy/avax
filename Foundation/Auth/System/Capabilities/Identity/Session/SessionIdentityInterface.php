<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Session;

/**
 * Interface SessionIdentityInterface within the Auth System.
 */
interface SessionIdentityInterface
{
    public function issue(int $userId) : void;
    public function getUserId() : int|null;
    public function clear() : void;
    public function check() : bool;
}
