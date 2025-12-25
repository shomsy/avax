<?php

declare(strict_types=1);

namespace Avax\Container\Containers;

use Exception;
use Avax\Config\Architecture\DDD\AppPath;
use Avax\Container\Contracts\ContainerInterface;
use Avax\Facade\Facades\Storage;
use Spatie\Ignition\Ignition;

/**
 * The Bootstrapper class initializes the application's core settings and dependencies.
 * This includes loading environment variables, helper functions, routes, and setting up error handling.
 * It is the entry point for configuring the application during the bootstrap process.
 */
readonly class Bootstrapper
{
    /**
     * File permissions required for the view cache directory.
     */
    private const int REQUIRED_PERMISSIONS = 0755;

    /**
     * @param string $envFilePath     Path to the environment variables file.
     * @param string $helpersFilePath Path to the helper functions file.
     */
    public function __construct(
        private string $envFilePath,
        private string $helpersFilePath,
    ) {}

    /**
     * Initializes the core components of the application.
     *
     * This method performs all essential boot-time logic required for the Foundation to operate:
     *
     * - Applies hardened session cookie configuration for secure session lifecycle.
     * - Loads environment variables from the configured `.env.php` bootstrap file.
     * - Loads global helper functions required across application layers.
     * - Registers the core Dependency Injection container instance globally.
     * - Initializes Spatie Ignition for enhanced exception reporting and IDE integration.
     * - Ensures the view cache directory exists with proper permissions, and clears any residual cache.
     *
     * This method MUST be called before handling any HTTP requests, session startup, or rendering views.
     * It guarantees that the runtime environment, security context, and application state are properly initialized.
     *
     * @param ContainerInterface $container The dependency injection container instance.
     *
     * @throws Exception If any of the following fail:
     *                   - Environment or helper boot file is missing
     *                   - View cache directory cannot be created or cleared
     *                   - File system permission errors
     */
    public function bootstrap(ContainerInterface $container) : void
    {
        $this->initializeSessionSecurity();
        $this->loadConfiguration(container: $container);
        $this->initializeErrorHandling();
        $this->initializeViewCacheDirectory();
    }

    /**
     * Configures secure PHP session cookie parameters.
     *
     * Applies hardened security settings for session cookies,
     * including secure transport, HTTP-only access, and SameSite enforcement.
     * This method MUST be called before session_start().
     */
    private function initializeSessionSecurity() : void
    {
        session_set_cookie_params(
            lifetime_or_options: [
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * Loads the configuration settings for the application.
     *
     * This includes environment variables and helper functions necessary for the application's operation.
     *
     * @throws \Exception
     */
    private function loadConfiguration(ContainerInterface $container) : void
    {
        $this->loadEnvironmentVariables();
        $this->loadHelperFunctions();
        appInstance(instance: $container);
    }

    /**
     * Loads environment variables from the specified file.
     */
    private function loadEnvironmentVariables() : void
    {
        require_once $this->envFilePath;
    }

    /**
     * Loads helper functions from the specified file.
     *
     * @throws \Exception
     */
    private function loadHelperFunctions() : void
    {
        if (! file_exists(filename: $this->helpersFilePath)) {
            throw new Exception(message: 'Helpers file not found: ' . $this->helpersFilePath);
        }

        require_once $this->helpersFilePath;
    }

    /**
     * Initializes error handling using the Spatie Ignition library.
     */
    private function initializeErrorHandling() : void
    {
        Ignition::make()
            ->shouldDisplayException(
                shouldDisplayException: env(key: 'APP_DEBUG') === 'true' || config(key: 'app.debug', default: false)
            ) // Display only in debug mode
            ->setTheme(theme: 'dark')
            ->register();
    }

    /**
     * Initializes the view cache directory.
     *
     * Ensures the view cache directory exists, has the correct permissions, and clears any existing cached files.
     * If the directory does not exist, it creates it with the necessary permissions.
     *
     * @throws Exception If the directory cannot be created, permissions cannot be set, or it cannot be cleared.
     */
    private function initializeViewCacheDirectory() : void
    {
        $viewCachePath = AppPath::getRoot() . 'storage/views';

        if (Storage::exists(path: $viewCachePath)) {
            $this->ensureDirectoryPermissions(path: $viewCachePath);
            $this->clearCacheDirectory(path: $viewCachePath);
        } else {
            $this->createViewCacheDirectory(path: $viewCachePath);
        }
    }

    /**
     * Ensures the specified directory has the required permissions.
     *
     * @param string $path The path to the directory.
     *
     * @throws Exception If permissions cannot be set.
     */
    private function ensureDirectoryPermissions(string $path) : void
    {
        if (! Storage::hasPermission(path: $path, permissions: self::REQUIRED_PERMISSIONS)
            && ! Storage::setPermissions(path: $path, permissions: self::REQUIRED_PERMISSIONS)) {
            throw new Exception(
                message: "Failed to set permissions for directory at " . $path . ". Check file system permissions."
            );
        }
    }

    /**
     * Clears the specified cache directory.
     *
     * @param string $path The path to the cache directory.
     *
     * @throws Exception If the directory cannot be cleared.
     */
    private function clearCacheDirectory(string $path) : void
    {
        if (! Storage::clear(directory: $path)) {
            throw new Exception(message: "Failed to clear cache in the view cache directory at " . $path . ".");
        }
    }

    /**
     * Creates the specified view cache directory with the necessary permissions.
     *
     * @param string $path The path to the view cache directory.
     *
     * @throws Exception If the directory cannot be created or permissions set.
     */
    private function createViewCacheDirectory(string $path) : void
    {
        if (! Storage::createDirectory(directory: $path)
            || ! Storage::setPermissions(path: $path, permissions: self::REQUIRED_PERMISSIONS)) {
            throw new Exception(message: "Failed to create directory at " . $path . " with the necessary permissions.");
        }
    }

}
