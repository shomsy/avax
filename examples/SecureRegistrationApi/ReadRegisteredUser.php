<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * ReadRegisteredUser — CQRS query side for reading the registered user projection.
 *
 * This is the read-side entry point for the reference flow.
 * Delegates to RegisteredUserView in-memory store.
 *
 * No generic CQRS folder. Ownership stays inside the reference flow.
 */
final class ReadRegisteredUser
{
    public function byUserId(string $userId): ?RegisteredUser
    {
        return RegisteredUserView::findByUserId($userId);
    }

    /**
     * @return list<RegisteredUser>
     */
    public function all(): array
    {
        return RegisteredUserView::all();
    }
}
