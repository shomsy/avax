<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Config\Architecture\DDD\AppPath;
use Avax\Container\Containers\Application;
use Avax\Container\Containers\Bootstrapper;
use Avax\Container\Containers\Kernel;
use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Router\Router;
use Avax\Logging\ErrorHandler;

/**
 * Class BootstrapServiceProvider
 *
 * This service provider is responsible for registering core services into the service container.
 * It follows the Dependency Inversion Principle, ensuring high-level modules depend on abstractions
 * rather than concrete implementations, enhancing flexibility and testability.
 */
class BootstrapServiceProvider extends ServiceProvider
{
    /**
     * Registers the necessary services into the service container.
     *
     * This method defines how services or configurations should be registered
     * within the service container, making them available for dependency injection
     * and use throughout the application.
     *
     */
    #[\Override]
    public function register() : void
    {
        // Register Bootstrapper singleton with required dependencies.
        $this->dependencyInjector->singleton(
            abstract: Bootstrapper::class,
            concrete: fn() : Bootstrapper => new Bootstrapper(
                envFilePath    : AppPath::getRoot() . 'env.php', // Dynamically resolves the env file path.
                helpersFilePath: AppPath::HELPERS_PATH->get(), // Dynamically resolves the helpers file path.
            )
        );

        // Register Application singleton with Kernel dependency.
        $this->dependencyInjector->singleton(
            abstract: Application::class,
            concrete: static fn() : Application => new Application()
        );

        // Register Kernel singleton with Router and ErrorHandler dependencies.
        $this->dependencyInjector->singleton(
            abstract: Kernel::class,
            concrete: fn() : Kernel => new Kernel(
                router      : $this->dependencyInjector->get(id: Router::class),
                errorHandler: $this->dependencyInjector->get(id: ErrorHandler::class)
            )
        );
    }

    /**
     * Starts the boot process for the class.
     * This method can be optionally overridden by derived classes to implement specific boot logic.
     *
     * @throws \Exception
     */
    #[\Override]
    public function boot() : void
    {
        /** @var Bootstrapper $bootstrapper */
        $bootstrapper = $this->dependencyInjector->get(id: Bootstrapper::class);
        $bootstrapper->bootstrap(container: $this->dependencyInjector);
    }
}