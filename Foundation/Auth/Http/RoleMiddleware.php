<?php

declare(strict_types=1);

namespace Avax\Auth\Interface\HTTP\Middleware;

use Avax\Auth\Contracts\Identity\IdentityInterface;
use Avax\Auth\Contracts\Identity\Subject\UserInterface;
use Avax\Auth\Contracts\IdentityInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Domain\Exception\AuthorizationException;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Middleware to ensure the user has the specified role.
 */
readonly class RoleMiddleware
{
    public function __construct(private IdentityInterface $guard) {}

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
