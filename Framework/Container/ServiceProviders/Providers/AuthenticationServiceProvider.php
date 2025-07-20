<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Providers;


use Gemini\Auth\Application\Action\LoginAction;
use Gemini\Auth\Application\Action\LogoutAction;
use Gemini\Auth\Application\Action\RegisterUserAction;
use Gemini\Auth\Application\Service\AuthenticationService;
use Gemini\Auth\Application\UseCase\API\ApiLoginUseCase;
use Gemini\Auth\Application\UseCase\API\ApiLogoutUseCase;
use Gemini\Auth\Application\UseCase\API\ApiRegisterUseCase;
use Gemini\Auth\Application\UseCase\Web\LoginUseCase;
use Gemini\Auth\Application\UseCase\Web\RetrieveCurrentUserUseCase;
use Gemini\Auth\Application\UseCases\Web\LogoutUseCase;
use Gemini\Auth\Application\UseCases\Web\RegisterUseCase;
use Gemini\Auth\Contracts\AuthenticationServiceInterface;
use Gemini\Auth\Contracts\CredentialsInterface;
use Gemini\Auth\Contracts\Identity\IdentityInterface;
use Gemini\Auth\Contracts\Identity\UserSourceInterface;
use Gemini\Auth\Domain\ValueObject\Credentials;
use Gemini\Auth\Infrastructure\Identity\Session\SessionIdentity;
use Gemini\Auth\Infrastructure\Security\PasswordHasher;
use Gemini\Auth\Infrastructure\User\DB\User;
use Gemini\Auth\Interface\HTTP\Middleware\AuthenticationMiddleware;
use Gemini\Auth\Interface\HTTP\Middleware\PermissionMiddleware;
use Gemini\Auth\Interface\HTTP\Middleware\RoleMiddleware;
use Gemini\Container\ServiceProviders\ServiceProvider;
use Gemini\Database\QueryBuilder\QueryBuilder;
use Gemini\HTTP\Security\CsrfTokenManager;

/**
 * Action provider for authentication services.
 *
 * This class handles the registration of dependencies related to the authentication
 * system within the service container. Each dependency is registered as a singleton,
 * meaning the same instance will be reused throughout the application lifecycle.
 */
class AuthenticationServiceProvider extends ServiceProvider
{

    /**
     * Register authentication-related services and their dependencies.
     *
     * This method binds various authentication-related classes and interfaces to the service container,
     * ensuring that they are instantiated properly and are available for dependency injection.
     *
     */
    public function register() : void
    {
        $this->dependencyInjector->singleton(abstract: PasswordHasher::class, concrete: PasswordHasher::class);
        $this->dependencyInjector->singleton(abstract: CredentialsInterface::class, concrete: Credentials::class);

        // Register the UserRegistrationService
        $this->dependencyInjector->singleton(
            abstract: RegisterUserAction::class,
            concrete: fn() => new RegisterUserAction(
                userProvider: $this->dependencyInjector->get(UserSourceInterface::class)
            )
        );

        // Register the RegisterUseCase
        $this->dependencyInjector->singleton(
            abstract: RegisterUseCase::class,
            concrete: fn() => new RegisterUseCase(
                registerService: $this->dependencyInjector->get(RegisterUserAction::class)
            )
        );


        $this->dependencyInjector->singleton(
            abstract: AuthenticationServiceInterface::class,
            concrete: fn() => new AuthenticationService(
                identity: $this->dependencyInjector->get(SessionIdentity::class)
            )
        );


        $this->dependencyInjector->singleton(abstract: IdentityInterface::class, concrete: fn(
        ) : SessionIdentity => new SessionIdentity(
            session     : $this->dependencyInjector->get(id: Session::class),
            userProvider: $this->dependencyInjector->get(id: User::class)
        ));
        $this->dependencyInjector->singleton(
            abstract: UserSourceInterface::class,
            concrete: fn() : User => new User(
                queryBuilder  : $this->dependencyInjector->get(id: QueryBuilder::class),
                passwordHasher: $this->dependencyInjector->get(PasswordHasher::class)
            )
        );
        $this->dependencyInjector->singleton(
            abstract: AuthenticationMiddleware::class,
            concrete: AuthenticationMiddleware::class
        );
        $this->dependencyInjector->singleton(
            abstract: PermissionMiddleware::class,
            concrete: PermissionMiddleware::class
        );
        $this->dependencyInjector->singleton(abstract: RoleMiddleware::class, concrete: RoleMiddleware::class);
        $this->dependencyInjector->singleton(
            abstract: ApiLoginUseCase::class,
            concrete: ApiLoginUseCase::class
        );
        $this->dependencyInjector->singleton(abstract: ApiLogoutUseCase::class, concrete: ApiLogoutUseCase::class);
        $this->dependencyInjector->singleton(abstract: ApiRegisterUseCase::class, concrete: ApiRegisterUseCase::class);

        $this->dependencyInjector->singleton(
            abstract: LoginUseCase::class,
            concrete: fn() => new LoginUseCase(
                loginAction: $this->dependencyInjector->get(LoginAction::class)
            )
        );
        $this->dependencyInjector->singleton(
            abstract: LogoutAction::class,
            concrete: fn() => new LogoutAction(
                authenticationService: $this->dependencyInjector->get(AuthenticationService::class),
                csrfTokenManager     : $this->dependencyInjector->get(CsrfTokenManager::class)
            )
        );
        $this->dependencyInjector->singleton(
            abstract: LogoutUseCase::class,
            concrete: fn() => new LogoutUseCase(
                logoutService: $this->dependencyInjector->get(LogoutAction::class)
            )
        );
        $this->dependencyInjector->singleton(
            abstract: RetrieveCurrentUserUseCase::class,
            concrete: RetrieveCurrentUserUseCase::class
        );
    }

    /**
     * Additional bootstrapping logic for authentication services.
     *
     * This method can be used to initialize additional configurations or dependencies required
     * for the authentication services after all services have been registered.
     */
    public function boot() : void
    {
        // Additional boot logic for auth services if required
    }
}