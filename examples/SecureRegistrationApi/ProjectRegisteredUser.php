<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * ProjectRegisteredUser — CQRS projection listener.
 *
 * Builds a RegisteredUserView read model from the UserRegistered event.
 *
 * Write side: registration flow emits UserRegistered.
 * Read side: RegisteredUserView holds the projection.
 *
 * No ListenerInterface required. Plain invokable class.
 * Registered through onEvent(UserRegistered::class)->do(...).
 */
final class ProjectRegisteredUser
{
    public function __invoke(UserRegistered $event): void
    {
        RegisteredUserView::set(new RegisteredUser(
            userId: $event->userId,
            email: $event->email,
            registeredAt: $event->registeredAt,
        ));
    }
}
