<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * RegisteredUserView — In-memory read model for registered user projection.
 *
 * CQRS read side: this is the queryable view.
 * Write side: ProjectRegisteredUser builds this from UserRegistered events.
 *
 * In-memory only. Reference/proof only — not production event sourcing.
 */
final class RegisteredUserView
{
    /** @var array<string, RegisteredUser> */
    private static array $users = [];

    public static function set(RegisteredUser $user): void
    {
        self::$users[$user->userId] = $user;
    }

    public static function findByUserId(string $userId): ?RegisteredUser
    {
        return self::$users[$userId] ?? null;
    }

    /**
     * @return list<RegisteredUser>
     */
    public static function all(): array
    {
        return array_values(self::$users);
    }

    public static function reset(): void
    {
        self::$users = [];
    }
}
