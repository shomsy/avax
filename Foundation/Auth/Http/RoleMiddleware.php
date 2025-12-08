<?php

declare(strict_types=1);

namespace Avax\Auth\Interface\HTTP\Middleware;

use Closure;
use Avax\Auth\Contracts\Identity\IdentityInterface;
use Avax\Auth\Contracts\Identity\Subject\UserInterface;
use Avax\Auth\Domain\Exception\AuthorizationException;

/**
 * Middleware to ensure the user has the specified role.
 */
class RoleMiddleware
{
    public function __construct(private readonly IdentityInterface $guard) {}

    /**
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next, string $role)
    {
        $user = $this->guard->user();

        if (! $user instanceof UserInterface || ! $user->hasRole(
                role: $role,
            )) {
            throw new AuthorizationException(message: sprintf('AccessControl lacks the required role: %s.', $role));
        }

        return $next($request);
    }
}
