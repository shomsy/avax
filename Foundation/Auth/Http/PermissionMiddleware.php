<?php

declare(strict_types=1);

namespace Avax\Auth\Interface\HTTP\Middleware;

use Closure;
use Avax\Auth\Contracts\Identity\IdentityInterface;
use Avax\Auth\Contracts\Identity\Subject\UserInterface;
use Avax\Auth\Domain\Exception\AuthorizationException;

/**
 * Middleware to ensure the user has the specified permission.
 */
readonly class PermissionMiddleware
{
    public function __construct(private IdentityInterface $guard) {}

    /**
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next, string $permission)
    {
        $user = $this->guard->user();

        if (! $user instanceof UserInterface || ! $user->hasPermission(
                permission: $permission,
            )) {
            throw new AuthorizationException(
                message: sprintf(
                             'AccessControl lacks the required permission: %s.',
                             $permission
                         )
            );
        }

        return $next($request);
    }
}
