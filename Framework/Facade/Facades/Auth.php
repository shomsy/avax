<?php

declare(strict_types=1);

namespace Gemini\Facade\Facades;

use Gemini\Auth\Contracts\AuthenticationServiceInterface;
use Gemini\Facade\BaseFacade;

/**
 * Facade for providing a simplified static interface to the AuthenticationService.
 *
 * This class leverages the static method calls provided by the base facade (`BaseFacade`) to
 * simplify interaction with the `AuthenticationService` implementation within the application.
 *
 * This facade is primarily aimed at providing syntactic convenience and ensuring strong
 * decoupling between application layers by interacting with the interface (DDD principle).
 *
 * Example usage:
 *
 * ```
 * Auth::login($credentials);  // Logs in a user with specific credentials.
 * Auth::check();              // Checks if a user is authenticated.
 * ```
 */
final class Auth extends BaseFacade
{
    /**
     * The unique key representing the authentication service in the application container.
     *
     * This key is used by the parent `BaseFacade` to dynamically resolve the concrete
     * implementation of the dependency injection container. The service contract (interface)
     * being represented is `AuthenticationServiceInterface`.
     *
     * @var string
     */
    protected static string $accessor = AuthenticationServiceInterface::class;
}