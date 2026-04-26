<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Auth\System\Auth as AuthSystem;
use Avax\Auth\System\Capabilities\Identity\User\UserInterface;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Facade\BaseFacade;

/**
 * Facade for providing a simplified static interface to the Authentication system.
 *
 * @method static UserInterface login(Credentials $credentials)
 * @method static void logout()
 * @method static UserInterface|null user()
 * @method static bool check()
 *
 * @see AuthSystem
 */
final class Auth extends BaseFacade
{
    /**
     * The unique key representing the authentication service in the application container.
     */
    protected static string $accessor = AuthSystem::class;
}
