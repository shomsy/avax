<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\User;

/**
 * Enum representing user roles within the Auth System.
 */
enum UserRole: string
{
    case ADMIN     = 'admin';
    case MODERATOR = 'moderator';
    case USER      = 'user';
    case GUEST     = 'guest';

    public function label() : string
    {
        return match ($this) {
            self::ADMIN     => 'Administrator',
            self::MODERATOR => 'Moderator',
            self::USER      => 'User',
            self::GUEST     => 'Guest',
        };
    }

    public function canAccess(self $required) : bool
    {
        return $this->hierarchyLevel() >= $required->hierarchyLevel();
    }

    public function hierarchyLevel() : int
    {
        return match ($this) {
            self::ADMIN     => 4,
            self::MODERATOR => 3,
            self::USER      => 2,
            self::GUEST     => 1,
        };
    }
}
