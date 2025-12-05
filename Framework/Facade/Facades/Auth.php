<?php

declare(strict_types=1);

namespace Gemini\Facade\Facades;

use Gemini\Auth\Contracts\AuthInterface;
use Gemini\Facade\BaseFacade;

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
 * @method static \Gemini\Auth\Contracts\UserInterface login(\Gemini\Auth\Data\Credentials $credentials)
 * @method static void logout()
 * @method static \Gemini\Auth\Contracts\UserInterface|null user()
 * @method static bool check()
 *
 * @see \Gemini\Auth\Authenticator
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