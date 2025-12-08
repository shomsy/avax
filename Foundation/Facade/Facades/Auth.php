<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Auth\Contracts\AuthInterface;
use Avax\Facade\BaseFacade;

/**
 * Facade for providing a simplified static interface to the Authentication system.
 *
 * Example usage:
 *
 * ```
 * Auth::login($credentials);  // Logs in a user.
 * Auth::check();              // Checks if a user is authenticated.
 * Auth::user();               // Returns current user.
 * ```
 *
 * @method static \Avax\Auth\Contracts\UserInterface login(\Avax\Auth\Data\Credentials $credentials)
 * @method static void logout()
 * @method static \Avax\Auth\Contracts\UserInterface|null user()
 * @method static bool check()
 *
 * @see \Avax\Auth\Authenticator
 */
final class Auth extends BaseFacade
{
    /**
     * The unique key representing the authentication service in the application container.
     *
     * @var string
     */
    protected static string $accessor = AuthInterface::class;
}